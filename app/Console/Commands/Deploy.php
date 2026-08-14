<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * The whole deploy recipe from docs/DEPLOYMENT.md in one command.
 *
 * Every step runs as its own subprocess rather than via $this->call(). That is
 * deliberate and load-bearing: step one replaces vendor/, and this process has
 * already loaded the old autoloader into memory. Calling later artisan steps
 * in-process would run them against a half-swapped vendor — exactly the stale-class
 * failure mode DEPLOYMENT.md documents (`PortableVisibilityConverter not found`).
 * A fresh `php artisan …` process per step always sees the vendor that is on disk now.
 */
class Deploy extends Command
{
    protected $signature = 'deploy
        {--skip-composer : Leave vendor/ untouched}
        {--skip-npm : Skip npm ci + npm run build}
        {--skip-seed : Skip db:seed}
        {--skip-media : Skip the logo/video/industry media imports}
        {--skip-cdn : Do not invalidate the CloudFront distribution}
        {--skip-permissions : Do not chown/chmod storage + bootstrap/cache}
        {--web-user= : Owner for the runtime-writable dirs (auto-detected otherwise)}
        {--maintenance : Put the site in maintenance mode for the duration}
        {--strict-preflight : Fail the deploy on preflight warnings, not just errors}
        {--dry-run : Print the plan and exit without running anything}';

    protected $description = 'Run the full deployment: composer, migrate, seed, assets, caches, media, sitemap, preflight';

    /** Directories the runtime writes to at request time, and the only ones chowned. */
    private const RUNTIME_DIRS = ['storage', 'bootstrap/cache'];

    /** @var list<array{label: string, cmd: list<string>, timeout: int}> */
    private array $ran = [];

    public function handle(): int
    {
        // Group-writable by default, so files this deploy creates as root (a fresh
        // laravel.log, a compiled view) stay writable by the web user's group rather
        // than landing 644 root-owned and 500ing the next request that touches them.
        umask(0002);

        $steps = $this->plan();

        if ($this->option('dry-run')) {
            $this->components->info('Deploy plan (dry run — nothing executed):');
            foreach ($steps as $i => $step) {
                $this->line(sprintf('  %2d. <fg=gray>%s</>', $i + 1, implode(' ', $step['cmd'])));
            }

            return self::SUCCESS;
        }

        $maintenance = (bool) $this->option('maintenance');

        if ($maintenance) {
            $this->runStep(['label' => 'Enter maintenance mode', 'cmd' => [PHP_BINARY, 'artisan', 'down'], 'timeout' => 60]);
        }

        try {
            foreach ($steps as $step) {
                if (! $this->runStep($step)) {
                    $this->newLine();
                    $this->components->error("Deploy aborted at: {$step['label']}");
                    $this->line('  <fg=gray>Nothing after this step ran. Fix the failure and re-run `php artisan deploy`.</>');

                    return self::FAILURE;
                }
            }
        } finally {
            // Always lift maintenance, including on failure — a deploy that dies
            // mid-way must not leave the site dark.
            if ($maintenance) {
                $this->runStep(['label' => 'Leave maintenance mode', 'cmd' => [PHP_BINARY, 'artisan', 'up'], 'timeout' => 60]);
            }
        }

        $this->newLine();
        $this->components->info(sprintf('Deploy complete — %d steps in %s.', count($this->ran), $this->totalDuration()));

        return self::SUCCESS;
    }

    /**
     * Ordered deploy steps, mirroring docs/DEPLOYMENT.md. Order is not cosmetic:
     * composer before anything that autoloads, assets before deploy:refresh (which
     * flushes the response cache that pins the old asset hashes), and preflight last
     * so it validates the config that config:cache actually baked.
     *
     * @return list<array{label: string, cmd: list<string>, timeout: int}>
     */
    private function plan(): array
    {
        $php = PHP_BINARY;
        $steps = [];

        if (! $this->option('skip-composer')) {
            $steps[] = [
                'label' => 'Install PHP dependencies',
                'cmd' => ['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--prefer-dist'],
                'timeout' => 900,
            ];
        }

        $steps[] = ['label' => 'Run migrations', 'cmd' => [$php, 'artisan', 'migrate', '--force'], 'timeout' => 900];

        if (! $this->option('skip-seed')) {
            // Seeders are idempotent (updateOrCreate keyed by slug/name) — safe on every deploy.
            $steps[] = ['label' => 'Seed database', 'cmd' => [$php, 'artisan', 'db:seed', '--force'], 'timeout' => 900];
        }

        if (! $this->option('skip-npm')) {
            $steps[] = ['label' => 'Install node modules', 'cmd' => ['npm', 'ci'], 'timeout' => 1800];
            $steps[] = ['label' => 'Build front-end assets', 'cmd' => ['npm', 'run', 'build'], 'timeout' => 1800];
        }

        // Copies Livewire's dist/ into public/vendor/livewire so the browser loads
        // livewire.min.js as a real file instead of through Livewire's PHP route.
        // Production nginx serves *.js from a static-file location whose miss path is
        // `error_page 404 -> /index.php`, and an error_page internal redirect KEEPS the
        // 404 status — so the PHP route returned the correct JS body under a 404, the
        // browser refused to execute it, Livewire never booted, and Filament's
        // `wire:submit` login form fell back to a native POST to /admin/login, which has
        // no POST route: every admin login died on "405 Method Not Allowed".
        // Re-run on every deploy, not once: the published copy is version-pinned, and a
        // Livewire upgrade that leaves it behind ships mismatched JS.
        $steps[] = [
            'label' => 'Publish Livewire assets',
            'cmd' => [$php, 'artisan', 'livewire:publish', '--assets'],
            'timeout' => 300,
        ];

        // Rebuilds config/route/view caches AND flushes the response cache. Must come
        // after the asset build: cached HTML references the previous build hashes, so
        // every cached page 404s its CSS/JS until this runs.
        $steps[] = ['label' => 'Refresh caches', 'cmd' => [$php, 'artisan', 'deploy:refresh'], 'timeout' => 300];

        if (! $this->option('skip-media')) {
            // Idempotent uploads: they skip objects already on the media disk. Without
            // them a fresh environment 404s the hero reel and renders blank industry cards.
            $steps[] = ['label' => 'Import client logos', 'cmd' => [$php, 'artisan', 'clients:import-legacy'], 'timeout' => 900];
            $steps[] = ['label' => 'Import videos', 'cmd' => [$php, 'artisan', 'videos:import'], 'timeout' => 1800];
            $steps[] = ['label' => 'Import industry covers', 'cmd' => [$php, 'artisan', 'industries:import'], 'timeout' => 900];
        }

        $steps[] = ['label' => 'Generate sitemap', 'cmd' => [$php, 'artisan', 'sitemap:generate'], 'timeout' => 300];
        // Immediately after the sitemap, because it reads the file that step just
        // wrote. No-ops without INDEXNOW_KEY, so it is safe to leave in the script
        // whether or not the environment has opted in.
        $steps[] = ['label' => 'Submit URLs to IndexNow', 'cmd' => [$php, 'artisan', 'indexnow:submit'], 'timeout' => 120];

        // After the asset build and the media imports, because it exists to clear
        // whatever those just replaced. Skipping it is how a corrected video kept
        // serving its stale preview from the edge for hours after origin was
        // right. The command exits 0 when no distribution is configured, and
        // warns rather than fails if the API call errors — the code and database
        // are already live by this point.
        if (! $this->option('skip-cdn')) {
            $steps[] = ['label' => 'Invalidate CDN', 'cmd' => [$php, 'artisan', 'app:invalidate-cdn'], 'timeout' => 300];
        }

        // Permissions come LAST of the mutating steps, not next to deploy:refresh.
        // Every artisan step above writes storage/logs/laravel.log as whoever runs the
        // deploy, so chowning earlier just gets undone by the steps that follow it —
        // the site then 500s on a deploy that reported success.
        if (! $this->option('skip-permissions') && ($owner = $this->runtimeOwner()) !== null) {
            $steps[] = [
                'label' => 'Hand '.implode(' + ', self::RUNTIME_DIRS)." to {$owner}",
                'cmd' => ['chown', '-R', "{$owner}:{$owner}", ...self::RUNTIME_DIRS],
                'timeout' => 120,
            ];
            // Directories only: 2775 = rwxrwxr-x + setgid. The x bit lets the web user
            // traverse; setgid means files created later (by a root-run artisan, or by
            // Blade compiling a view at request time) inherit the web user's group
            // rather than the creator's, so this stops being a recurring outage.
            $steps[] = [
                'label' => 'Set runtime dir permissions (775 + setgid)',
                'cmd' => ['find', ...self::RUNTIME_DIRS, '-type', 'd', '-exec', 'chmod', '2775', '{}', '+'],
                'timeout' => 120,
            ];
            // Files get 664, never 775. Git records only the executable bit, so a
            // blanket `chmod -R 775` flips the tracked .gitignore placeholders in these
            // dirs from 100644 to 100755 and every deploy then reports 11 modified
            // files and can block `git pull`. 664 reads as 100644 — no diff — and data
            // files should not be executable anyway.
            $steps[] = [
                'label' => 'Set runtime file permissions (664)',
                'cmd' => ['find', ...self::RUNTIME_DIRS, '-type', 'f', '-exec', 'chmod', '664', '{}', '+'],
                'timeout' => 120,
            ];
        }

        $preflight = [$php, 'artisan', 'app:preflight'];
        if ($this->option('strict-preflight')) {
            $preflight[] = '--strict';
        }
        // Last on purpose: it validates the config the app will actually serve, and a
        // non-zero exit here fails the whole deploy.
        $steps[] = ['label' => 'Preflight checks', 'cmd' => $preflight, 'timeout' => 300];

        return $steps;
    }

    /**
     * The user PHP-FPM/nginx runs as, which must own storage/ and bootstrap/cache.
     * Returns null when the chown would be pointless or impossible — a non-root
     * deploy user cannot chown, and local macOS has no separate web user.
     */
    private function runtimeOwner(): ?string
    {
        if ($explicit = $this->option('web-user')) {
            return (string) $explicit;
        }

        if (PHP_OS_FAMILY !== 'Linux') {
            return null;
        }

        if (! function_exists('posix_geteuid') || posix_geteuid() !== 0) {
            $this->components->warn('Not running as root — skipping the storage chown. Run with sudo, or hand it to the web user yourself.');

            return null;
        }

        // Ask the process table who is actually serving, rather than guessing by
        // name. Both `www` and `www-data` exist on plenty of boxes — aaPanel
        // installs serve as `www` while a leftover Debian package supplies a
        // `www-data` account — so a name lookup in a fixed order silently chowns
        // storage to a user that never touches it, and the site 500s on a deploy
        // that reported success.
        if ($detected = $this->webServerUser()) {
            return $detected;
        }

        // Nothing serving right now (a first deploy, or a stopped pool), so fall
        // back to whichever account exists.
        foreach (['www-data', 'www'] as $candidate) {
            if (function_exists('posix_getpwnam') && posix_getpwnam($candidate) !== false) {
                $this->components->warn("No running web process found; assuming '{$candidate}'. Pass --web-user=<name> if that is wrong.");

                return $candidate;
            }
        }

        $this->components->warn('Could not detect the web user — skipping chown. Pass --web-user=<name> to set it.');

        return null;
    }

    /** The non-root user running php-fpm, nginx or apache, if one is up. */
    private function webServerUser(): ?string
    {
        $ps = @shell_exec('ps -eo user:32,comm 2>/dev/null');

        if (! is_string($ps) || $ps === '') {
            return null;
        }

        $counts = [];

        foreach (explode("\n", $ps) as $line) {
            $parts = preg_split('/\s+/', trim($line), 2);

            if (count($parts) !== 2) {
                continue;
            }

            [$user, $comm] = $parts;

            if ($user === 'root' || $user === '' || ! preg_match('/(php-fpm|php_fpm|nginx|apache2|httpd)/i', $comm)) {
                continue;
            }

            // Most worker processes wins — masters run as root and are skipped
            // above, so this lands on the pool user.
            $counts[$user] = ($counts[$user] ?? 0) + 1;
        }

        if ($counts === []) {
            return null;
        }

        arsort($counts);

        return (string) array_key_first($counts);
    }

    /** @param array{label: string, cmd: list<string>, timeout: int} $step */
    private function runStep(array $step): bool
    {
        $this->newLine();
        $this->line("<fg=cyan>▸</> {$step['label']}");
        $this->line('  <fg=gray>'.implode(' ', $step['cmd']).'</>');

        $process = new Process($step['cmd'], base_path(), null, null, $step['timeout']);
        $started = microtime(true);

        // Stream output live: a deploy that appears hung is indistinguishable from a
        // deploy that is slow, and composer/npm are both slow.
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        $seconds = microtime(true) - $started;
        $this->ran[] = $step + ['seconds' => $seconds];

        if (! $process->isSuccessful()) {
            $this->line(sprintf('  <fg=red>✗ failed after %s (exit %d)</>', $this->humanise($seconds), $process->getExitCode()));

            return false;
        }

        $this->line(sprintf('  <fg=green>✓ done in %s</>', $this->humanise($seconds)));

        return true;
    }

    private function totalDuration(): string
    {
        return $this->humanise(array_sum(array_column($this->ran, 'seconds')));
    }

    private function humanise(float $seconds): string
    {
        return $seconds < 60
            ? sprintf('%.1fs', $seconds)
            : sprintf('%dm %02ds', (int) ($seconds / 60), (int) $seconds % 60);
    }
}
