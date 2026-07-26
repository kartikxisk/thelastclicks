<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Push the bundled industry cover images to the media disk so CloudFront serves
 * them and the industry cards are never blank on a fresh deploy. The seeder
 * points each industry's image_url at `industries/<slug>.jpg`, which resolves on
 * the media disk — an uploaded hero in the admin still overrides it.
 *
 * Idempotent: an object already on the disk is skipped unless --force.
 */
class ImportIndustryImages extends Command
{
    protected $signature = 'industries:import {--force : Re-upload even if the object already exists}';

    protected $description = 'Upload public/industries/*.jpg to the media disk (CloudFront)';

    public function handle(): int
    {
        $root = public_path('industries');

        if (! is_dir($root)) {
            $this->warn('Nothing to import — public/industries/ does not exist.');

            return self::SUCCESS;
        }

        $disk = (string) config('media-library.disk_name', 'public');
        $files = glob($root.'/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
        $uploaded = 0;
        $skipped = 0;

        foreach ($files as $absolute) {
            $key = 'industries/'.basename($absolute);

            try {
                if (! $this->option('force') && Storage::disk($disk)->exists($key)) {
                    $this->line("skip     {$key}");
                    $skipped++;

                    continue;
                }

                $stream = fopen($absolute, 'rb');
                Storage::disk($disk)->writeStream($key, $stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                $uploaded++;
                $this->info("uploaded {$key}");
            } catch (Throwable $e) {
                $this->error("FAILED   {$key} — ".$e->getMessage());

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info("Uploaded {$uploaded} file(s). Skipped {$skipped}.");

        return self::SUCCESS;
    }
}
