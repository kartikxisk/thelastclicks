<?php

namespace App\Console\Commands\Outreach;

use App\Mail\OutreachMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Sends the outreach queue, one mail at a time, slowly, and records what left.
 *
 * Dry-run is the default and --live is deliberately awkward. Everything here is
 * irreversible: a sent mail cannot be recalled, and a burst of them against a
 * domain that normally sends a handful a day costs reputation that takes weeks
 * to earn back.
 */
class SendOutreach extends Command
{
    protected $signature = 'outreach:send
        {--live : Actually send. Without this, nothing leaves the machine}
        {--limit=0 : Stop after N messages (0 = the whole queue, up to max_per_run)}
        {--step= : Only send this step (first-touch, follow-up-1, follow-up-2)}
        {--only= : Restrict the run to this address, which must already be in the queue}
        {--test= : Send one real message to this address using the first queue row\'s copy}';

    protected $description = 'Send the personalised outreach queue with throttling and suppression';

    /** @var list<array<string, string>> */
    private array $sent = [];

    public function handle(): int
    {
        $live = (bool) $this->option('live');

        if ($live && $this->call('outreach:preflight') !== self::SUCCESS) {
            $this->error('Preflight failed. Nothing sent.');

            return self::FAILURE;
        }

        $queue = $this->readQueue();
        if ($queue === null) {
            return self::FAILURE;
        }

        if ($this->option('test')) {
            return $this->sendTest($queue);
        }

        $suppressed = $this->readSuppression();
        $already = $this->readAlreadySent();

        $pending = [];
        foreach ($queue as $row) {
            $email = strtolower(trim($row['email'] ?? ''));
            $step = trim($row['step'] ?? '');

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->line('  <fg=yellow>skip</>  invalid address: '.($row['email'] ?? '(blank)'));

                continue;
            }
            if ($this->option('only') && $email !== strtolower((string) $this->option('only'))) {
                continue;
            }
            if ($this->option('step') && $step !== $this->option('step')) {
                continue;
            }
            if (! in_array($step, (array) config('outreach.steps'), true)) {
                $this->line("  <fg=yellow>skip</>  {$email} — unknown step '{$step}'");

                continue;
            }
            if (in_array($email, $suppressed, true)) {
                $this->line("  <fg=yellow>skip</>  {$email} — on the suppression list");

                continue;
            }
            // The append-only sent log, not the queue, is the authority on what has
            // already gone out. A re-exported queue or a half-finished run must never
            // mail the same person the same step twice.
            if (in_array($email.'|'.$step, $already, true)) {
                $this->line("  <fg=yellow>skip</>  {$email} — {$step} already sent");

                continue;
            }

            $pending[] = $row;
        }

        $limit = (int) $this->option('limit');
        $ceiling = (int) config('outreach.max_per_run');
        $cap = $limit > 0 ? min($limit, $ceiling) : $ceiling;

        if (count($pending) > $cap) {
            $this->warn('Queue has '.count($pending)." eligible, capping this run at {$cap}.");
            $pending = array_slice($pending, 0, $cap);
        }

        if ($pending === []) {
            $this->info('Nothing to send.');

            return self::SUCCESS;
        }

        $throttle = (int) config('outreach.throttle_seconds');
        $eta = $this->humanDuration(count($pending) * $throttle);

        $this->newLine();
        $this->line(($live ? '<fg=red>LIVE</>' : '<fg=cyan>DRY RUN</>')
            .' — '.count($pending)." message(s), ~{$throttle}s apart, about {$eta}");
        $this->newLine();

        if ($live && ! $this->confirmLive(count($pending))) {
            $this->info('Aborted. Nothing sent.');

            return self::SUCCESS;
        }

        $ok = 0;
        $failed = 0;

        foreach ($pending as $i => $row) {
            $email = strtolower(trim($row['email']));
            $step = trim($row['step']);
            $subject = $this->subjectFor($row, $step);
            $messageId = $this->messageId($email, $step);
            $inReplyTo = $step === 'first-touch' ? null : ($row['thread_message_id'] ?: null);

            $label = sprintf('%3d/%d  %-38s %-12s', $i + 1, count($pending), $email, $step);

            if (! $live) {
                $this->line("  <fg=cyan>would</> {$label} {$subject}");
                $ok++;

                continue;
            }

            try {
                Mail::to($email)->send(new OutreachMail($row, $step, $subject, $messageId, $inReplyTo));

                $this->line("  <fg=green>sent</>  {$label} {$subject}");
                $this->record($email, $step, 'sent', $messageId, $subject, '');
                $ok++;
            } catch (Throwable $e) {
                $this->line("  <fg=red>FAIL</>  {$label} {$e->getMessage()}");
                $this->record($email, $step, 'failed', $messageId, $subject, $e->getMessage());
                $failed++;
            }

            // Flush after every message, not at the end: a crash or a Ctrl-C
            // mid-run must not lose the record of what already went out, or the
            // next run re-mails those people.
            $this->flush();

            if ($i < count($pending) - 1) {
                sleep($this->throttleFor());
            }
        }

        $this->newLine();
        if (! $live) {
            $this->info("Dry run complete — {$ok} message(s) would be sent. Re-run with --live to send.");

            return self::SUCCESS;
        }

        $this->info("Sent {$ok}, failed {$failed}. Log: ".str_replace(base_path().'/', '', (string) config('outreach.sent_path')));
        $this->line('Next: python3 marketing-sales/build_leads.py --import-results');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Send one real message to an arbitrary address, using a queue row's copy.
     *
     * --only cannot do this: it filters the queue, so an address that is not
     * already a prospect matches nothing and the run reports "Nothing to send".
     * That made "post a test to yourself before mailing 62 strangers" impossible
     * to actually follow, which is the one step worth never skipping.
     *
     * Deliberately not written to the sent log: no prospect received anything,
     * so recording a send would suppress the real one later.
     *
     * @param  list<array<string, string>>  $queue
     */
    private function sendTest(array $queue): int
    {
        $to = strtolower(trim((string) $this->option('test')));

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error("Not a valid address: {$to}");

            return self::FAILURE;
        }

        // A test send leaves the building like any other, so it gets the same
        // identity check. Testing through a broken From address proves nothing
        // except that the mail did not arrive.
        if ($this->call('outreach:preflight') !== self::SUCCESS) {
            $this->error('Preflight failed. Nothing sent.');

            return self::FAILURE;
        }

        $step = (string) ($this->option('step') ?: 'first-touch');
        if (! in_array($step, (array) config('outreach.steps'), true)) {
            $this->error("Unknown step '{$step}'.");

            return self::FAILURE;
        }

        $row = $queue[0] ?? null;
        if ($row === null) {
            $this->error('Queue is empty — nothing to preview with.');

            return self::FAILURE;
        }

        $row['step'] = $step;
        $subject = $this->subjectFor($row, $step);
        $messageId = $this->messageId($to, $step);

        // Follow-up copy is written to sit under a quoted original, so a test of
        // it threads onto a message id that does not exist. Harmless, and the
        // body is what is being checked.
        $inReplyTo = $step === 'first-touch' ? null : 'test-thread@'.substr(strrchr($to, '@'), 1);

        $this->newLine();
        $this->line("<fg=red>TEST SEND</> — one real message to {$to}");
        $this->line("  step:     {$step}");
        $this->line("  subject:  {$subject}");
        $this->line("  copy for: {$row['organizer']} / {$row['event']}");
        $this->newLine();

        if ($this->ask('Type SEND to confirm') !== 'SEND') {
            $this->info('Aborted. Nothing sent.');

            return self::SUCCESS;
        }

        try {
            Mail::to($to)->send(new OutreachMail($row, $step, $subject, $messageId, $inReplyTo));
        } catch (Throwable $e) {
            $this->error('Failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Sent to {$to}. Not recorded — no prospect was contacted.");
        $this->line('Check it landed in the inbox, not Promotions or Spam.');

        return self::SUCCESS;
    }

    private function confirmLive(int $count): bool
    {
        $this->warn("This sends {$count} real email(s) to real people. It cannot be undone.");

        // A typed word rather than a y/n: this prompt is the last thing between a
        // bad queue and 62 strangers' inboxes, and y/n is muscle memory.
        return $this->ask('Type SEND to confirm') === 'SEND';
    }

    /**
     * @return list<array<string, string>>|null
     */
    private function readQueue(): ?array
    {
        $path = (string) config('outreach.queue_path');

        if (! is_readable($path)) {
            $this->error('No queue at '.str_replace(base_path().'/', '', $path));
            $this->line('Run: python3 marketing-sales/build_leads.py --export-queue');

            return null;
        }

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, 0, ',', '"', '');
        if ($headers === false) {
            fclose($handle);
            $this->error('Queue is empty.');

            return null;
        }

        $rows = [];
        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if ($line === [null] || $line === []) {
                continue;
            }
            $rows[] = array_map(
                static fn ($v) => (string) $v,
                array_combine($headers, array_pad(array_slice($line, 0, count($headers)), count($headers), ''))
            );
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function readSuppression(): array
    {
        $path = (string) config('outreach.suppression_path');
        if (! is_readable($path)) {
            return [];
        }

        $lines = array_map('trim', file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []);

        return array_values(array_filter(array_map(
            static fn (string $l) => str_starts_with($l, '#') ? '' : strtolower($l),
            $lines
        )));
    }

    /**
     * @return list<string> "email|step" pairs already in the sent log.
     */
    private function readAlreadySent(): array
    {
        $path = (string) config('outreach.sent_path');
        if (! is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, 0, ',', '"', '');
        if ($headers === false) {
            fclose($handle);

            return [];
        }

        $pairs = [];
        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $row = array_combine($headers, array_pad(array_slice($line, 0, count($headers)), count($headers), ''));
            // Only a successful send blocks a retry -- a failure should be retried.
            if (($row['status'] ?? '') === 'sent') {
                $pairs[] = strtolower((string) $row['email']).'|'.$row['step'];
            }
        }
        fclose($handle);

        return $pairs;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function subjectFor(array $row, string $step): string
    {
        if ($step === 'first-touch') {
            return $row['event'].' — post-production for the floor footage';
        }

        // Follow-ups must reuse the original subject verbatim, prefixed once, or
        // the client shows them as a separate conversation and the thread breaks.
        $original = $row['thread_subject'] ?: ($row['event'].' — post-production for the floor footage');

        return str_starts_with($original, 'Re: ') ? $original : 'Re: '.$original;
    }

    private function messageId(string $email, string $step): string
    {
        $domain = substr(strrchr((string) config('mail.from.address'), '@') ?: '@thelastclicks.com', 1);

        return 'outreach-'.$step.'-'.substr(sha1($email.$step.microtime()), 0, 20).'.'.Str::random(6).'@'.$domain;
    }

    private function throttleFor(): int
    {
        $base = (int) config('outreach.throttle_seconds');
        $jitter = (int) config('outreach.throttle_jitter');

        return max(1, $base + random_int(-$jitter, $jitter));
    }

    private function record(string $email, string $step, string $status, string $messageId, string $subject, string $error): void
    {
        $this->sent[] = [
            'sent_at' => now()->toDateString(),
            'email' => $email,
            'step' => $step,
            'status' => $status,
            'message_id' => $messageId,
            'subject' => $subject,
            'error' => $error,
        ];
    }

    private function flush(): void
    {
        if ($this->sent === []) {
            return;
        }

        $path = (string) config('outreach.sent_path');
        @mkdir(dirname($path), 0755, true);

        $new = ! file_exists($path);
        $handle = fopen($path, 'a');
        if ($new) {
            fputcsv($handle, ['sent_at', 'email', 'step', 'status', 'message_id', 'subject', 'error'], ',', '"', '');
        }
        foreach ($this->sent as $row) {
            fputcsv($handle, array_values($row), ',', '"', '');
        }
        fclose($handle);

        $this->sent = [];
    }

    private function humanDuration(int $seconds): string
    {
        if ($seconds < 90) {
            return $seconds.'s';
        }
        if ($seconds < 3600) {
            return round($seconds / 60).' min';
        }

        return round($seconds / 3600, 1).' hr';
    }
}
