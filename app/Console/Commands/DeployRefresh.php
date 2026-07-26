<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeployRefresh extends Command
{
    protected $signature = 'deploy:refresh';

    protected $description = 'Post-deploy: rebuild framework caches, relink storage, flush the response cache';

    /**
     * Run after `composer install` / `npm run build` on every deploy. Bundles the
     * artisan-only steps so nobody forgets responsecache:clear (which is what makes
     * new content — logos, hero images — actually appear on the cached public pages).
     */
    public function handle(): int
    {
        // Clear stale compiled caches first, then rebuild them from the freshly
        // deployed code. `optimize` caches config + events + routes + views in one go.
        $steps = [
            'optimize:clear',
            'optimize',
            // The important one: without it, cached HTML keeps serving old images/copy.
            'responsecache:clear',
        ];

        foreach ($steps as $command) {
            $this->newLine();
            $this->components->task("php artisan {$command}", fn () => $this->call($command) === self::SUCCESS);
        }

        // storage:link errors if the symlink already exists, so only create it when missing.
        if (! is_link(public_path('storage')) && ! is_dir(public_path('storage'))) {
            $this->newLine();
            $this->components->task('php artisan storage:link', fn () => $this->call('storage:link') === self::SUCCESS);
        }

        // Filament v3 component/icon cache — present only when Filament is installed.
        if ($this->getApplication()?->has('filament:optimize')) {
            $this->newLine();
            $this->components->task('php artisan filament:optimize', fn () => $this->call('filament:optimize') === self::SUCCESS);
        }

        $this->newLine();
        $this->components->info('Deploy caches refreshed.');

        return self::SUCCESS;
    }
}
