<?php

namespace App\Console\Commands;

use Aws\CloudFront\CloudFrontClient;
use Illuminate\Console\Command;
use Throwable;

/**
 * Clear the CDN after anything that changes what sits behind it.
 *
 * Repairing the media paths on S3 fixed nothing visible for hours: CloudFront
 * kept serving the objects it had already cached, so every portfolio video went
 * on playing a 6-second silent preview while the full film sat correct at
 * origin. Uploading to the bucket is only half of publishing.
 *
 * `/*` is a single invalidation path as far as billing is concerned, so there is
 * no reason to enumerate keys and risk missing one.
 *
 *     php artisan app:invalidate-cdn
 *     php artisan app:invalidate-cdn --wait
 */
class InvalidateCdn extends Command
{
    protected $signature = 'app:invalidate-cdn
        {--paths=/* : Comma-separated paths to invalidate}
        {--wait : Block until the invalidation reports Completed}';

    protected $description = 'Invalidate the CloudFront distribution in front of the media disk';

    public function handle(): int
    {
        $distribution = (string) config('services.cloudfront.distribution_id');

        if ($distribution === '') {
            // Not every environment sits behind CloudFront, and a deploy should
            // not fail because one does not.
            $this->line('No CLOUDFRONT_DISTRIBUTION_ID set — nothing to invalidate.');

            return self::SUCCESS;
        }

        $paths = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('paths')))));

        try {
            $client = new CloudFrontClient([
                'version' => 'latest',
                // CloudFront is a global service; its endpoint always lives here
                // regardless of where the bucket is.
                'region' => 'us-east-1',
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            $result = $client->createInvalidation([
                'DistributionId' => $distribution,
                'InvalidationBatch' => [
                    // Unique per call: CloudFront treats a repeated reference as
                    // a retry of the same request and returns the old one.
                    'CallerReference' => 'deploy-'.now()->format('YmdHis').'-'.bin2hex(random_bytes(4)),
                    'Paths' => ['Quantity' => count($paths), 'Items' => $paths],
                ],
            ]);

            $id = $result['Invalidation']['Id'];
            $this->info("Invalidation {$id} created for ".implode(', ', $paths));

            if ($this->option('wait')) {
                $this->line('Waiting for it to complete…');
                $client->waitUntil('InvalidationCompleted', [
                    'DistributionId' => $distribution,
                    'Id' => $id,
                ]);
                $this->info('Completed.');
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            // A stale CDN is bad, a failed deploy is worse — the code and
            // database are already live by the time this runs.
            $this->warn('Invalidation failed: '.$e->getMessage());
            $this->line('Assets are deployed; the CDN will catch up at TTL, or invalidate by hand.');

            return self::SUCCESS;
        }
    }
}
