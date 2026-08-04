<?php

namespace App\Console\Commands;

use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Point every medialibrary row at the object that actually is its file.
 *
 * The library's path is `{media id}/{file name}`, and the ids have moved: the
 * database was rebuilt at some point while the bucket kept its old layout, so
 * row 3 asks for `3/anchor-....mp4` while the 36 MB original sits at
 * `20/anchor-....mp4`. Every video resolved to a key that was not there, and S3
 * answers 403 rather than 404 for a missing key when the caller has no
 * s3:ListBucket — which is why this read as a permissions problem.
 *
 * Matching is by file name across the whole bucket, and where a name appears
 * more than once the LARGEST candidate wins. That matters: each film exists
 * twice, as a full cut and as a 6-second silent preview under
 * portfolio/previews. The card plays the preview by design (Work::previewVideoUrl)
 * and the lightbox plays the full one, so picking by size is what keeps those
 * two apart. An earlier version of this command matched only the previews
 * prefix and quietly made every lightbox play the 6-second clip.
 *
 * The copy is server-side (S3 COPY), so nothing is downloaded or re-uploaded and
 * no bytes leave the bucket.
 *
 *     php artisan app:repair-media-paths --dry-run
 *     php artisan app:repair-media-paths
 */
class RepairMediaPaths extends Command
{
    protected $signature = 'app:repair-media-paths
        {--dry-run : Report what would be copied and change nothing}
        {--prefer-smallest : Match the smallest candidate rather than the largest}';

    protected $description = 'Copy media objects to the path medialibrary derives from their id';

    public function handle(): int
    {
        $diskName = (string) config('media-library.disk_name', 's3');
        $disk = Storage::disk($diskName);
        $bucket = (string) config("filesystems.disks.{$diskName}.bucket");
        $preferSmallest = (bool) $this->option('prefer-smallest');
        $dry = (bool) $this->option('dry-run');

        if ($bucket === '') {
            $this->error("No bucket configured for the '{$diskName}' disk.");

            return self::FAILURE;
        }

        // One listing of the bucket, indexed by file name. Probing per row would
        // be thousands of round trips and still could not find a file whose
        // directory no longer matches any id.
        $this->line('Indexing bucket…');
        $byName = [];
        foreach ($disk->allFiles() as $path) {
            $byName[basename($path)][] = $path;
        }
        $this->line('  '.count($byName).' distinct file names.');
        $this->newLine();

        $copied = 0;
        $already = 0;
        $unresolved = [];

        foreach (Media::orderBy('id')->get() as $media) {
            $target = $media->id.'/'.$media->file_name;
            $candidates = $byName[$media->file_name] ?? [];

            // Rank by size so the full cut beats its 6-second preview. Without
            // this the lightbox ends up playing the card's clip.
            $ranked = [];
            foreach ($candidates as $path) {
                $ranked[$path] = $disk->size($path);
            }
            $preferSmallest ? asort($ranked) : arsort($ranked);
            $best = array_key_first($ranked);

            if ($best === null) {
                $unresolved[] = "id={$media->id} {$media->file_name} (no object anywhere in the bucket)";

                continue;
            }

            // Already correct, and already the right one of the candidates.
            if ($best === $target) {
                $already++;

                continue;
            }

            if ($disk->exists($target) && $disk->size($target) === $ranked[$best]) {
                $already++;

                continue;
            }

            $source = $best;
            $mb = round($ranked[$best] / 1048576, 1);

            if ($dry) {
                $this->line("  would copy {$source} ({$mb} MB) -> {$target}");
                $copied++;

                continue;
            }

            try {
                // Raw CopyObject rather than $disk->copy(). Flysystem's copy
                // reads the source object's ACL first to preserve visibility,
                // and this bucket has ACLs disabled (object ownership is bucket
                // owner enforced), so GetObjectAcl fails and the copy aborts —
                // the same class of trap the s3 disk config already warns about
                // for PortableVisibilityConverter. CopyObject is server-side and
                // never looks at an ACL.
                // getClient() lives on the s3 adapter, not the FilesystemAdapter
                // contract, so it is reached dynamically.
                /** @var S3Client $client */
                $client = $disk->{'getClient'}();
                $client->copyObject([
                    'Bucket' => $bucket,
                    'Key' => $target,
                    'CopySource' => rawurlencode($bucket.'/'.$source),
                ]);
                $copied++;
                $this->line("  copied {$source} ({$mb} MB) -> {$target}");
            } catch (Throwable $e) {
                // One unwritable object should not abort a repair that has
                // already fixed the rest.
                $unresolved[] = "id={$media->id} {$media->file_name} ({$e->getMessage()})";
            }
        }

        $this->newLine();
        $this->info(($dry ? 'Would copy ' : 'Copied ').$copied.' object(s).');
        $this->line("Already in place: {$already}");

        if ($unresolved !== []) {
            $this->warn('Unresolved: '.count($unresolved));
            foreach (array_slice($unresolved, 0, 10) as $row) {
                $this->line('  '.$row);
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
