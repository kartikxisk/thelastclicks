<?php

namespace App\Console\Commands;

use App\Support\AppUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Deploy gate for the settings that fail silently.
 *
 * A wrong APP_URL does not throw — it just publishes canonical tags, og:url and
 * every asset() pointing at a host crawlers cannot reach, and nobody notices
 * until rankings do. Same for APP_DEBUG left on, or an unreachable media disk.
 * Run this after deploying; a non-zero exit should stop the release.
 */
class Preflight extends Command
{
    protected $signature = 'app:preflight {--strict : Treat warnings as failures too}';

    protected $description = 'Verify the environment is safe to serve publicly (APP_URL, debug, media disk, queue)';

    /** @var list<array{string, string, string}> */
    protected array $results = [];

    public function handle(): int
    {
        $isProduction = app()->environment('production');

        $this->checkAppUrl($isProduction);
        $this->checkDebug($isProduction);
        $this->checkMediaDisk();
        $this->checkQueue($isProduction);

        $this->newLine();
        $this->table(['', 'Check', 'Detail'], $this->results);

        $failed = $this->countOf('FAIL');
        $warned = $this->countOf('WARN');

        if ($failed > 0) {
            $this->error("{$failed} check(s) failed — do not serve this build publicly.");

            return self::FAILURE;
        }

        if ($warned > 0 && $this->option('strict')) {
            $this->error("{$warned} warning(s), and --strict was given.");

            return self::FAILURE;
        }

        $this->info($warned > 0 ? "Passed with {$warned} warning(s)." : 'All checks passed.');

        return self::SUCCESS;
    }

    protected function checkAppUrl(bool $isProduction): void
    {
        $url = AppUrl::current();

        if ($url === '') {
            $this->fail_('APP_URL', 'Not set. Canonicals and og:url will be wrong.');

            return;
        }

        if (AppUrl::isLocal($url)) {
            // Local value in production poisons every canonical tag on the site.
            $this->record($isProduction ? 'FAIL' : 'WARN', 'APP_URL', "Local value ({$url}) — must be the public domain in production.");

            return;
        }

        if ($isProduction && ! AppUrl::isSecure($url)) {
            $this->fail_('APP_URL', "Not https ({$url}). Canonicals must point at the secure origin.");

            return;
        }

        $this->pass('APP_URL', $url);
    }

    protected function checkDebug(bool $isProduction): void
    {
        if ($isProduction && config('app.debug')) {
            $this->fail_('APP_DEBUG', 'Enabled in production — leaks stack traces, env values and queries.');

            return;
        }

        $this->pass('APP_DEBUG', config('app.debug') ? 'on (non-production)' : 'off');
    }

    protected function checkMediaDisk(): void
    {
        $disk = (string) config('media-library.disk_name', 'public');

        try {
            Storage::disk($disk)->exists('.preflight-probe');
            $this->pass('Media disk', "{$disk} reachable");
        } catch (Throwable $e) {
            // Uploads (client logos, industry media, work galleries) all land here.
            $this->fail_('Media disk', "{$disk} unreachable: ".$e->getMessage());
        }
    }

    protected function checkQueue(bool $isProduction): void
    {
        if ($isProduction && config('queue.default') === 'sync') {
            // Quote notifications send inline, so slow SMTP stalls the visitor's
            // form submit and a mail failure can surface as a 500 after the lead
            // has already been captured.
            $this->record('WARN', 'Queue', 'sync in production — mail sends inside the web request.');

            return;
        }

        $this->pass('Queue', (string) config('queue.default'));
    }

    protected function pass(string $check, string $detail): void
    {
        $this->record('OK', $check, $detail);
    }

    protected function fail_(string $check, string $detail): void
    {
        $this->record('FAIL', $check, $detail);
    }

    protected function record(string $status, string $check, string $detail): void
    {
        $this->results[] = [$status, $check, $detail];
    }

    protected function countOf(string $status): int
    {
        return count(array_filter($this->results, fn (array $r): bool => $r[0] === $status));
    }
}
