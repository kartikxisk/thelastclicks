<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Upload full-length work videos over the previews currently standing in for them.
 *
 * The bucket only ever held 6-second silent cuts under portfolio/previews for
 * the 53 portfolio videos; the full films are not on S3 anywhere. app:repair-
 * media-paths copied those previews into the paths medialibrary derives so the
 * lightbox stopped 403ing, which fixed the breakage but left every work playing
 * a 6-second clip with no audio.
 *
 * This puts the real files in. Matching is by file name against the media rows,
 * so a directory of masters named as they are in the library lands in the right
 * places; anything unmatched is reported rather than guessed at.
 *
 *     php artisan app:upload-work-videos --from=/path/to/masters --dry-run
 *     php artisan app:upload-work-videos --from=/path/to/masters
 */
class UploadWorkVideos extends Command
{
    protected $signature = 'app:upload-work-videos
        {--from= : Directory holding the full-length files}
        {--dry-run : Report what would be uploaded and change nothing}';

    protected $description = 'Replace preview stand-ins with full-length work videos from a local directory';

    public function handle(): int
    {
        $from = rtrim((string) $this->option('from'), '/');
        $dry = (bool) $this->option('dry-run');

        if ($from === '' || ! is_dir($from)) {
            $this->error('Pass --from=/path/to/masters (a readable directory).');

            return self::FAILURE;
        }

        $diskName = (string) config('media-library.disk_name', 's3');
        $disk = Storage::disk($diskName);

        $uploaded = 0;
        $skipped = 0;
        $unmatched = [];

        foreach (Media::where('mime_type', 'like', 'video/%')->orderBy('id')->get() as $media) {
            $local = $from.'/'.$media->file_name;

            if (! is_file($local)) {
                $unmatched[] = $media->file_name;

                continue;
            }

            $target = $media->id.'/'.$media->file_name;
            $localSize = (int) filesize($local);

            // A file the same size as what is already up there is the same file;
            // re-uploading gigabytes to no effect is worth avoiding.
            if ($disk->exists($target) && $disk->size($target) === $localSize) {
                $skipped++;

                continue;
            }

            $mb = round($localSize / 1048576, 1);

            if ($dry) {
                $this->line("  would upload {$media->file_name} ({$mb} MB) -> {$target}");
                $uploaded++;

                continue;
            }

            try {
                $stream = fopen($local, 'rb');
                $disk->put($target, $stream, ['ContentType' => $media->mime_type]);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                // Keep the row honest about what is actually stored.
                $media->size = $localSize;
                $media->save();

                $uploaded++;
                $this->line("  uploaded {$media->file_name} ({$mb} MB)");
            } catch (Throwable $e) {
                $unmatched[] = $media->file_name.' ('.$e->getMessage().')';
            }
        }

        $this->newLine();
        $this->info(($dry ? 'Would upload ' : 'Uploaded ').$uploaded.' file(s).');
        $this->line("Already current: {$skipped}");

        if ($unmatched !== []) {
            $this->warn('No local file for '.count($unmatched).':');
            foreach (array_slice($unmatched, 0, 15) as $name) {
                $this->line('  '.$name);
            }
            $this->newLine();
            $this->line('Name the masters exactly as above and re-run.');
        }

        if (! $dry && $uploaded > 0) {
            $this->newLine();
            $this->warn('CloudFront still has the old objects cached. Invalidate the paths, or the previews keep serving until the TTL expires.');
        }

        return self::SUCCESS;
    }
}
