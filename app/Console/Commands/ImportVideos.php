<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Push the bundled video assets to the media disk so CloudFront serves them
 * instead of the app server. Video is by far the heaviest thing the site ships;
 * streaming it through PHP/nginx wastes the CDN we already pay for.
 *
 * Idempotent — an object already on the disk is skipped unless --force.
 */
class ImportVideos extends Command
{
    protected $signature = 'videos:import
        {--only= : Only import paths containing this substring}
        {--force : Re-upload even if the object already exists}';

    protected $description = 'Upload public/videos/** to the media disk (CloudFront)';

    public function handle(): int
    {
        $root = public_path('videos');

        if (! is_dir($root)) {
            $this->warn('Nothing to import — public/videos/ does not exist.');

            return self::SUCCESS;
        }

        $disk = (string) config('media-library.disk_name', 'public');
        $only = (string) $this->option('only');
        $uploaded = 0;
        $skipped = 0;
        $bytes = 0;

        foreach ($this->files($root) as $absolute) {
            $key = 'videos/'.ltrim(str_replace($root, '', $absolute), '/');

            if ($only !== '' && ! str_contains($key, $only)) {
                continue;
            }

            try {
                if (! $this->option('force') && Storage::disk($disk)->exists($key)) {
                    $this->line("skip     {$key}");
                    $skipped++;

                    continue;
                }

                $stream = fopen($absolute, 'rb');
                // Streamed, not file_get_contents — a 90MB master would otherwise
                // have to sit in PHP memory in full.
                Storage::disk($disk)->writeStream($key, $stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                $size = (int) filesize($absolute);
                $bytes += $size;
                $uploaded++;
                $this->info(sprintf('uploaded %-42s %s', $key, $this->human($size)));
            } catch (Throwable $e) {
                $this->error("FAILED   {$key} — ".$e->getMessage());

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info("Uploaded {$uploaded} file(s), {$this->human($bytes)}. Skipped {$skipped}.");

        return self::SUCCESS;
    }

    /** @return list<string> */
    protected function files(string $root): array
    {
        $out = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile() && ! str_starts_with($file->getFilename(), '.')) {
                $out[] = $file->getPathname();
            }
        }

        sort($out);

        return $out;
    }

    protected function human(int $bytes): string
    {
        return $bytes > 1048576
            ? round($bytes / 1048576, 1).'MB'
            : round($bytes / 1024).'KB';
    }
}
