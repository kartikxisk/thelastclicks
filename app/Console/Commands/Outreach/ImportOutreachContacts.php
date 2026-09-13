<?php

namespace App\Console\Commands\Outreach;

use App\Filament\Imports\OutreachContactImporter;
use App\Models\OutreachContact;
use Illuminate\Console\Command;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Throwable;

/**
 * Load contacts from a sheet on disk.
 *
 * The admin has an upload button for the same job; this is for the first load
 * (the original list lived in a gitignored workbook that the panel cannot see)
 * and for anything scripted. Both paths converge on the same matching rule, so
 * a contact cannot be created twice by using one and then the other.
 */
class ImportOutreachContacts extends Command
{
    protected $signature = 'outreach:import
        {path : A .csv or .xlsx with at least an email column}
        {--sheet= : Which sheet to read, when the workbook has several}
        {--dry-run : Parse and report without writing}';

    protected $description = 'Import outreach contacts from a CSV or XLSX file';

    /** Sheet headings we accept for each column, lowercased. */
    private const ALIASES = [
        'email' => ['email', 'email address', 'e-mail', 'organizer_email', 'organiser email'],
        'organizer' => ['organizer', 'organiser', 'company', 'organizer_name', 'name'],
        'organizer_short' => ['organizer_short', 'greeting name', 'greeting'],
        'event' => ['event', 'next event', 'event name', 'title'],
        'event_city' => ['event_city', 'city', 'location'],
        'event_dates' => ['event_dates', 'dates'],
        'event_starts_on' => ['event_starts_on', 'next event on', 'start', 'start date'],
        'event_count' => ['event_count', 'events'],
        'locations' => ['locations'],
        'status' => ['status'],
        'priority' => ['priority'],
        'owner' => ['owner'],
        'notes' => ['notes'],
    ];

    public function handle(): int
    {
        $path = (string) $this->argument('path');

        if (! is_readable($path)) {
            $this->error("Cannot read {$path}");

            return self::FAILURE;
        }

        try {
            [$headers, $rows] = $this->read($path);
        } catch (Throwable $e) {
            $this->error('Could not parse the sheet: '.$e->getMessage());

            return self::FAILURE;
        }

        $map = $this->mapColumns($headers);

        if (! isset($map['email'])) {
            $this->error('No email column found. Headings seen: '.implode(', ', $headers));

            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $n => $row) {
            $values = [];
            foreach ($map as $field => $i) {
                $values[$field] = trim((string) ($row[$i] ?? ''));
            }

            $email = strtolower($values['email']);
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->line("  <fg=yellow>skip</>  row {$n}: invalid address '{$values['email']}'");
                $skipped++;

                continue;
            }

            $contact = OutreachContact::firstOrNew(['email' => $email]);
            $exists = $contact->exists;

            foreach ($values as $field => $value) {
                if ($field === 'email' || $value === '') {
                    continue;
                }
                // A blank cell means "no opinion", not "clear this". Overwriting a
                // hand-typed note with an empty column is how a re-upload quietly
                // destroys the team's own work.
                $contact->{$field} = $field === 'event_starts_on' ? $this->date($value) : $value;
            }

            if (blank($contact->organizer_short) && filled($contact->organizer)) {
                $contact->organizer_short = OutreachContactImporter::greetingFor($contact->organizer);
            }

            if (blank($contact->status)) {
                $contact->status = 'Not Contacted';
            }

            // Never walk a live contact back to the start because a stale sheet
            // says so — that would restart the sequence for someone mid-flight.
            if ($exists && $contact->isDirty('status')
                && $contact->getOriginal('status') !== 'Not Contacted'
                && $contact->status === 'Not Contacted') {
                $contact->status = $contact->getOriginal('status');
            }

            if (! $this->option('dry-run')) {
                $contact->save();
            }

            $exists ? $updated++ : $created++;
        }

        $verb = $this->option('dry-run') ? 'would be' : '';
        $this->newLine();
        $this->info(trim("{$created} created, {$updated} updated, {$skipped} skipped {$verb}"));

        if ($this->option('dry-run')) {
            $this->line('Dry run — nothing written.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>}
     */
    private function read(string $path): array
    {
        // openspout ships with Filament, so .xlsx costs no extra dependency --
        // and a marketing team will hand you .xlsx far more often than .csv.
        $reader = str_ends_with(strtolower($path), '.xlsx') ? new XlsxReader : new CsvReader;
        $reader->open($path);

        $headers = [];
        $rows = [];

        $wanted = strtolower(trim((string) $this->option('sheet')));
        $fallback = null;

        // Not "the first sheet": a real workbook opens on a dashboard or a cover
        // tab, and the contacts live several sheets in. Worse, more than one
        // sheet can carry an email column -- a per-event sheet listing the same
        // organiser eight times will match just as happily as the deduplicated
        // contact sheet, and silently leave every contact holding whichever
        // event happened to sort last. So prefer a sheet that names itself for
        // contacts, and only fall back to the first match.
        foreach ($reader->getSheetIterator() as $sheet) {
            $name = strtolower(trim($sheet->getName()));
            $sheetHeaders = [];
            $sheetRows = [];

            foreach ($sheet->getRowIterator() as $i => $row) {
                $cells = array_map($this->cellToString(...), $row->getCells());

                if ($sheetHeaders === []) {
                    // Sheets exported from this project carry a title block above
                    // the real headings, so the first non-empty row is not
                    // necessarily the header. Find the row that mentions an email.
                    if ($this->looksLikeHeader($cells)) {
                        $sheetHeaders = $cells;
                    }

                    continue;
                }

                if (array_filter($cells, static fn ($c) => trim($c) !== '') !== []) {
                    $sheetRows[$i] = $cells;
                }
            }

            if ($sheetHeaders === []) {
                continue;
            }

            $match = $wanted !== '' ? $name === $wanted : str_contains($name, 'contact');

            if ($match) {
                $reader->close();

                return [$sheetHeaders, $sheetRows];
            }

            $fallback ??= [$sheetHeaders, $sheetRows];
        }

        $reader->close();

        return $fallback ?? [$headers, $rows];
    }

    /**
     * Flatten one cell to a string.
     *
     * A date cell in an .xlsx comes back as a DateTime, not a scalar — treating
     * anything non-scalar as blank silently dropped every event date on import,
     * which is exactly the field the mail copy personalises on.
     */
    private function cellToString(object $cell): string
    {
        $value = $cell->getValue();

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return is_scalar($value) ? (string) $value : '';
    }

    /** @param  list<string>  $cells */
    private function looksLikeHeader(array $cells): bool
    {
        foreach ($cells as $cell) {
            if (in_array(strtolower(trim($cell)), self::ALIASES['email'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $headers
     * @return array<string, int>
     */
    private function mapColumns(array $headers): array
    {
        $map = [];

        foreach ($headers as $i => $heading) {
            $key = strtolower(trim($heading));

            foreach (self::ALIASES as $field => $aliases) {
                if (! isset($map[$field]) && in_array($key, $aliases, true)) {
                    $map[$field] = $i;
                    break;
                }
            }
        }

        return $map;
    }

    private function date(string $value): ?string
    {
        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }
}
