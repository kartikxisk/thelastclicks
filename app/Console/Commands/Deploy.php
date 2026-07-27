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
            // 775, never 777 — the right owner plus group-write is enough.
            $steps[] = [
                'label' => 'Set runtime dir permissions',
                'cmd' => ['chmod', '-R', '775', ...self::RUNTIME_DIRS],
                'timeout' => 120,
            ];
            // setgid on the directories: files created later (by a root-run artisan, or
            // by Blade compiling a view at request time) inherit the web user's group
            // instead of the creator's, so 775 keeps them writable and this stops being
            // a recurring outage.
            $steps[] = [
                'label' => 'Set setgid so new files inherit the group',
                'cmd' => ['find', ...self::RUNTIME_DIRS, '-type', 'd', '-exec', 'chmod', 'g+s', '{}', '+'],
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

        // aaPanel/BT installs run as `www`; Ubuntu/Forge as `www-data`.
        foreach (['www-data', 'www'] as $candidate) {
            if (function_exists('posix_getpwnam') && posix_getpwnam($candidate) !== false) {
                return $candidate;
            }
        }

        $this->components->warn('Could not detect the web user — skipping chown. Pass --web-user=<name> to set it.');

        return null;
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
