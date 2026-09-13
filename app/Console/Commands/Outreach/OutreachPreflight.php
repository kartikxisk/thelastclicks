<?php

namespace App\Console\Commands\Outreach;

use Illuminate\Console\Command;

/**
 * Refuses to let a send happen against a broken sending identity.
 *
 * This exists because MAIL_FROM_ADDRESS sat as "info@thelastlclicks.com" -- a
 * typo'd domain that is NXDOMAIN -- while every SPF, DKIM and DMARC record was
 * published on thelastclicks.com. The auth stack was perfect and irrelevant:
 * DMARC is evaluated against the From header's domain, and that domain did not
 * exist. Under p=reject the mail is refused outright, so quote replies had been
 * dying silently. Nothing in the app noticed. This command notices.
 */
class OutreachPreflight extends Command
{
    protected $signature = 'outreach:preflight {--strict : Treat warnings as failures}';

    protected $description = 'Verify the sending identity and DNS auth before any outreach goes out';

    private int $failures = 0;

    private int $warnings = 0;

    public function handle(): int
    {
        $this->info('Outreach preflight');
        $this->newLine();

        $from = (string) config('mail.from.address');

        // Credentials live under the active mailer, not at config('mail.username')
        // -- that path silently resolves to null and skips the alignment check,
        // which is precisely the check that catches a mismatched From domain.
        $mailer = (string) config('mail.default');
        $username = (string) config("mail.mailers.{$mailer}.username");

        // A domain that does not exist has no SPF, DKIM or DMARC to look up, and
        // every one of those queries waits out the full resolver timeout before
        // failing -- two minutes of stalling to re-learn what we already know.
        if ($this->checkFromAddress($from, $username)) {
            $this->checkDns($this->domainOf($from));
        } else {
            $this->newLine();
            $this->line('DNS checks skipped — fix the From address first.');
        }

        $this->checkFiles();
        $this->checkThrottle();

        $this->newLine();

        if ($this->failures > 0) {
            $this->error("FAILED — {$this->failures} blocking issue(s). Do not send.");

            return self::FAILURE;
        }

        if ($this->warnings > 0 && $this->option('strict')) {
            $this->error("FAILED — {$this->warnings} warning(s) under --strict.");

            return self::FAILURE;
        }

        $this->info($this->warnings > 0
            ? "PASSED with {$this->warnings} warning(s)."
            : 'PASSED — sending identity is sound.');

        return self::SUCCESS;
    }

    /**
     * @return bool Whether the From domain resolves, and so is worth querying further.
     */
    private function checkFromAddress(string $from, string $username): bool
    {
        if ($from === '') {
            $this->bad('MAIL_FROM_ADDRESS is empty.');

            return false;
        }

        if (! filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $this->bad("MAIL_FROM_ADDRESS is not a valid address: {$from}");

            return false;
        }

        $this->ok("From address: {$from}");

        $fromDomain = $this->domainOf($from);

        // The From domain must actually resolve. A domain with no records at all
        // cannot pass DMARC, and cannot receive the replies this campaign exists
        // to generate.
        if (! checkdnsrr($fromDomain, 'A') && ! checkdnsrr($fromDomain, 'MX')) {
            $this->bad("From domain does not resolve (no A, no MX): {$fromDomain}");
            $this->line('         A typo here silently kills every mail the app sends.');

            return false;
        }

        $this->ok("From domain resolves: {$fromDomain}");

        if (! checkdnsrr($fromDomain, 'MX')) {
            $this->warned("From domain has no MX — replies to {$from} will bounce.");
        }

        if ($username !== '' && filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $authDomain = $this->domainOf($username);

            // Relaxed DMARC alignment allows the organisational domains to match
            // rather than the exact hostnames, but if these differ outright the
            // SPF leg cannot align and DKIM is carrying the whole policy alone.
            if ($authDomain !== $fromDomain) {
                $this->bad("From domain ({$fromDomain}) differs from SMTP auth domain ({$authDomain}).");
            } else {
                $this->ok("SMTP auth domain matches From domain: {$authDomain}");
            }
        }

        return true;
    }

    private function checkDns(string $domain): void
    {
        $this->newLine();
        $this->line("DNS for {$domain}");

        $txt = @dns_get_record($domain, DNS_TXT) ?: [];
        $spf = null;
        foreach ($txt as $record) {
            $value = $record['txt'] ?? '';
            if (str_starts_with(strtolower($value), 'v=spf1')) {
                $spf = $value;
            }
        }

        if ($spf === null) {
            $this->bad('No SPF record.');
        } else {
            $this->ok("SPF: {$spf}");
            if (str_contains($spf, '+all')) {
                $this->bad('SPF ends in +all — that authorises the entire internet to send as you.');
            }
        }

        $dmarc = @dns_get_record('_dmarc.'.$domain, DNS_TXT) ?: [];
        $policy = null;
        foreach ($dmarc as $record) {
            $value = $record['txt'] ?? '';
            if (str_starts_with(strtolower($value), 'v=dmarc1')) {
                $policy = $value;
            }
        }

        if ($policy === null) {
            $this->warned('No DMARC record. Mail still delivers, but nothing protects the domain from spoofing.');
        } else {
            $this->ok("DMARC: {$policy}");
            if (str_contains($policy, 'p=reject')) {
                $this->line('         p=reject — any alignment failure is refused, not junked. No slack.');
            }
        }

        // GoDaddy publishes DKIM as CNAMEs to onsecureserver.net rather than TXT
        // on the domain, so a plain TXT lookup on the selector finds nothing and
        // reports a false negative. Follow the CNAME.
        $found = [];
        foreach ((array) config('outreach.dkim_selectors', []) as $selector) {
            $host = $selector.'._domainkey.'.$domain;
            $records = @dns_get_record($host, DNS_TXT + DNS_CNAME) ?: [];
            foreach ($records as $record) {
                $value = $record['txt'] ?? '';
                if (str_contains(strtolower($value), 'v=dkim1')) {
                    $found[] = $selector;
                    break;
                }
                if (($record['type'] ?? '') === 'CNAME') {
                    $target = @dns_get_record($record['target'], DNS_TXT) ?: [];
                    foreach ($target as $inner) {
                        if (str_contains(strtolower($inner['txt'] ?? ''), 'v=dkim1')) {
                            $found[] = $selector;
                            break 2;
                        }
                    }
                }
            }
        }

        if ($found === []) {
            $this->warned('No DKIM key found at the configured selectors ('
                .implode(', ', (array) config('outreach.dkim_selectors', [])).').');
            $this->line('         Under p=reject that leaves SPF as the only auth leg — one forward and the mail is refused.');
        } else {
            $this->ok('DKIM: '.count($found).' selector(s) publishing keys ('.implode(', ', $found).')');
        }
    }

    private function checkFiles(): void
    {
        $this->newLine();
        $this->line('Files');

        $queue = (string) config('outreach.queue_path');
        if (! is_readable($queue)) {
            $this->warned('No queue at '.$this->relative($queue).' — run the exporter first.');
        } else {
            $lines = file($queue, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $rows = max(0, count($lines) - 1);
            $this->ok("Queue: {$rows} row(s) pending");
            $this->checkListQuality($lines);
        }

        $suppression = (string) config('outreach.suppression_path');
        if (! file_exists($suppression)) {
            // Not a failure -- an empty list is legitimate on the first run. But a
            // path that exists and cannot be read is, because the send would then
            // silently mail people who asked to be left alone.
            $this->warned('No suppression list at '.$this->relative($suppression).' — it will be created on first opt-out.');
        } elseif (! is_readable($suppression)) {
            $this->bad('Suppression list exists but is not readable: '.$this->relative($suppression));
        } else {
            // Comments are not addresses. Counting them reports a list that looks
            // populated when nobody is actually suppressed.
            $count = count(array_filter(
                array_map('trim', file($suppression) ?: []),
                static fn (string $line) => $line !== '' && ! str_starts_with($line, '#'),
            ));
            $this->ok("Suppression list: {$count} address(es)");
        }
    }

    /**
     * Flag the addresses most likely to cost reputation rather than earn a reply.
     *
     * Gmail wants complaints under 0.30%. On a list this size a single report is
     * well over that, so which addresses are on it matters more than the copy.
     *
     * @param  list<string>  $lines  Raw CSV lines, header first.
     */
    private function checkListQuality(array $lines): void
    {
        $prefixes = (array) config('outreach.role_prefixes', []);
        $role = 0;
        $free = 0;
        $total = 0;

        foreach (array_slice($lines, 1) as $line) {
            if (! preg_match('/[\w.+-]+@[\w.-]+\.\w+/', $line, $m)) {
                continue;
            }
            $total++;
            [$local, $domain] = explode('@', strtolower($m[0]), 2);

            foreach ($prefixes as $prefix) {
                if ($local === $prefix || str_starts_with($local, $prefix.'.') || str_starts_with($local, $prefix.'@')) {
                    $role++;
                    break;
                }
            }

            if (preg_match('/^(gmail|yahoo|hotmail|outlook|live|rediffmail)\./', $domain)) {
                $free++;
            }
        }

        if ($total === 0) {
            return;
        }

        if ($role > 0) {
            $this->warned("{$role} of {$total} are role inboxes (accounts@, info@, contact@ …).");
            $this->line('         A pitch in a finance inbox is the likeliest message on the list to be');
            $this->line('         reported, and they rarely reply. Consider dropping them.');
        }

        if ($free > 0) {
            $this->line("  <fg=cyan>note</>   {$free} of {$total} are Gmail/Yahoo — where the spam rate is measured.");
        }
    }

    private function checkThrottle(): void
    {
        $this->newLine();
        $this->line('Throttle');

        $seconds = (int) config('outreach.throttle_seconds');
        if ($seconds < 30) {
            $this->warned("Throttle is {$seconds}s. Below ~30s a small domain starts to look like a blaster.");
        } else {
            $this->ok("{$seconds}s between sends (+/- ".config('outreach.throttle_jitter').'s jitter)');
        }
    }

    private function domainOf(string $email): string
    {
        return strtolower(substr(strrchr($email, '@') ?: '', 1));
    }

    private function relative(string $path): string
    {
        return str_replace(base_path().'/', '', $path);
    }

    private function ok(string $message): void
    {
        $this->line("  <fg=green>OK</>     {$message}");
    }

    private function warned(string $message): void
    {
        $this->warnings++;
        $this->line("  <fg=yellow>WARN</>   {$message}");
    }

    private function bad(string $message): void
    {
        $this->failures++;
        $this->line("  <fg=red>FAIL</>   {$message}");
    }
}
