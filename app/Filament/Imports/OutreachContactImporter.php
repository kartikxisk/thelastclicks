<?php

namespace App\Filament\Imports;

use App\Models\OutreachContact;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

class OutreachContactImporter extends Importer
{
    protected static ?string $model = OutreachContact::class;

    /**
     * @return array<ImportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email:rfc'])
                // Uploads come out of a spreadsheet, where "  Foo@Bar.com " is
                // normal. Matching an existing contact is done on this value, so
                // it has to be normalised before the lookup, not after.
                ->fillRecordUsing(function (OutreachContact $record, string $state): void {
                    $record->email = Str::lower(trim($state));
                })
                ->example('events@example.com'),

            ImportColumn::make('organizer')
                ->rules(['max:255'])
                ->example('Messe Frankfurt Trade Fairs India'),

            ImportColumn::make('organizer_short')
                ->label('Greeting name')
                ->rules(['max:255'])
                ->example('Messe Frankfurt team'),

            ImportColumn::make('event')->rules(['max:255'])->example('Media Expo New Delhi 2026'),
            ImportColumn::make('event_city')->rules(['max:255'])->example('New Delhi'),
            ImportColumn::make('event_dates')->rules(['max:255'])->example('17 Sep 2026'),
            ImportColumn::make('event_starts_on')->rules(['date'])->example('2026-09-17'),
            ImportColumn::make('event_count')->numeric()->rules(['integer', 'min:1'])->example('8'),
            ImportColumn::make('locations')->rules(['max:255'])->example('New Delhi, Mumbai'),

            ImportColumn::make('status')
                ->rules(['in:'.implode(',', OutreachContact::STATUSES)])
                ->example('Not Contacted'),

            ImportColumn::make('priority')->rules(['max:32'])->example('High'),
            ImportColumn::make('owner')->rules(['max:255'])->example('Kartik'),
            ImportColumn::make('notes')->example('Met at the Delhi show'),
        ];
    }

    /**
     * Match on email so a re-uploaded sheet updates people rather than
     * duplicating them — the same list gets exported and re-imported constantly,
     * and a second row for someone mid-sequence would mail them twice.
     */
    public function resolveRecord(): ?OutreachContact
    {
        $email = Str::lower(trim((string) ($this->data['email'] ?? '')));

        return OutreachContact::firstOrNew(['email' => $email]);
    }

    protected function beforeSave(): void
    {
        /** @var OutreachContact $record */
        $record = $this->record;

        // Never let an upload walk a live contact backwards. Someone who has been
        // mailed cannot become "Not Contacted" because the sheet was stale, or
        // the sequence starts again from the top.
        if ($record->exists && $record->isDirty('status')) {
            $original = $record->getOriginal('status');

            if ($original !== 'Not Contacted' && $record->status === 'Not Contacted') {
                $record->status = $original;
            }
        }

        if (blank($record->status)) {
            $record->status = 'Not Contacted';
        }

        if (blank($record->organizer_short) && filled($record->organizer)) {
            $record->organizer_short = static::greetingFor($record->organizer);
        }

        $record->source_batch = 'import-'.$this->import->getKey();
    }

    /**
     * Turn a legal entity name into something you can greet.
     *
     * "MESSE FRANKFURT TRADE FAIRS INDIA PRIVATE LIMITED" -> "Messe Frankfurt team".
     * A sheet almost never carries a person's name, and "Hi MESSE FRANKFURT
     * TRADE FAIRS INDIA PRIVATE LIMITED," is the tell that a message is a blast.
     */
    public static function greetingFor(string $name): string
    {
        $drop = ['pvt', 'private', 'ltd', 'limited', 'llp', 'inc', 'co', 'corp',
            'corporation', 'company', 'india', 'indian', 'pte', 'gmbh', '&', 'and'];
        $genericTail = ['trade', 'fairs', 'fair', 'exhibitions', 'exhibition', 'expo',
            'expos', 'markets', 'market', 'events', 'event', 'group', 'business',
            'consultants', 'consultancy', 'services', 'solutions', 'international',
            'global', 'management', 'media', 'communications', 'ventures', 'enterprises'];

        $words = preg_split('/[\s,]+/', trim($name)) ?: [];
        $words = array_values(array_filter($words, fn ($w) => $w !== ''
            && ! in_array(Str::lower(rtrim($w, '.')), $drop, true)));

        // Only re-case the shouty ones; a name already in mixed case (McKinsey,
        // IIFA) is likelier correct as typed than as title-cased.
        $words = array_map(fn ($w) => $w === Str::upper($w) ? Str::title($w) : $w, $words);

        while (count($words) > 1 && in_array(Str::lower(end($words)), $genericTail, true)) {
            array_pop($words);
        }

        $short = trim(implode(' ', array_slice($words, 0, 3)), ' -–—&');

        if ($short === '') {
            return 'there';
        }

        return Str::endsWith(Str::lower($short), 'team') ? $short : $short.' team';
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $rows = number_format($import->successful_rows);
        $body = "Imported {$rows} contact(s).";

        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' row(s) failed — download the report to see why.';
        }

        return $body;
    }
}
