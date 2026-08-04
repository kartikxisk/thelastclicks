<?php

namespace App\Console\Commands;

use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Put medialibrary's video objects where medialibrary looks for them.
 *
 * Every video attached through a MediaItem resolved to a URL that 403s. The
 * files were uploaded — they sit at `portfolio/previews/{file}` — but
 * medialibrary's default path generator is `{media id}/{file}`, so the rows
 * pointed at a prefix that never had them. S3 answers 403 rather than 404 for a
 * missing key when the caller has no s3:ListBucket, which is why this read as a
 * permissions problem for so long.
 *
 * It stayed invisible because CloudFront still had the old objects cached and
 * kept serving them; the moment those entries expired every portfolio video
 * would have gone dark.
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
        {--source=portfolio/previews : Prefix the originals were uploaded under}';

    protected $description = 'Copy media objects to the path medialibrary derives from their id';

    public function handle(): int
    {
        $diskName = (string) config('media-library.disk_name', 's3');
        $disk = Storage::disk($diskName);
        $bucket = (string) config("filesystems.disks.{$diskName}.bucket");
        $sourcePrefix = trim((string) $this->option('source'), '/');
        $dry = (bool) $this->option('dry-run');

        if ($bucket === '') {
            $this->error("No bucket configured for the '{$diskName}' disk.");

            return self::FAILURE;
        }

        $copied = 0;
        $already = 0;
        $unresolved = [];

        foreach (Media::orderBy('id')->get() as $media) {
            $target = $media->id.'/'.$media->file_name;

            if ($disk->exists($target)) {
                $already++;

                continue;
            }

            $source = $sourcePrefix.'/'.$media->file_name;

            if (! $disk->exists($source)) {
                $unresolved[] = "id={$media->id} {$media->file_name}";

                continue;
            }

            if ($dry) {
                $this->line("  would copy {$source} -> {$target}");
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
                $this->line("  copied {$source} -> {$target}");
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
