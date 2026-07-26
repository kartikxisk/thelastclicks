<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportBrandLogo extends Command
{
    protected $signature = 'brand:import-logo {path? : Logo file to upload (defaults to public/logo.png)}';

    protected $description = 'Upload a logo file to the media disk and set it as the site brand logo';

    public function handle(): int
    {
        $path = $this->argument('path') ?: public_path('logo.png');

        if (! is_file($path)) {
            $this->error("No file at {$path}");

            return self::FAILURE;
        }

        $disk = config('filament.default_filesystem_disk', config('filesystems.default'));
        $target = 'branding/'.basename($path);

        $contents = file_get_contents($path);
        if ($contents === false) {
            $this->error("Could not read {$path}");

            return self::FAILURE;
        }

        // Never assume success — a bad path here would point the whole site at a broken
        // image. Handles both failure modes: an exception ('throw' => true) and a false
        // return (if AWS_THROW is turned off).
        try {
            $written = Storage::disk($disk)->put($target, $contents);
        } catch (\Throwable $e) {
            $this->error("Upload to disk [{$disk}] failed: ".$this->explain($e->getMessage()));

            return $this->fallbackToLocal($path);
        }

        if ($written === false) {
            $this->error("Disk [{$disk}] refused the write (credentials / bucket permissions).");

            return $this->fallbackToLocal($path);
        }

        SiteSetting::set('brand_logo', $target);

        $this->info("Uploaded to [{$disk}] {$target}");
        $this->info('Live at: '.SiteSetting::brandLogoUrl());

        // Read-back is best-effort: the app IAM user may lack s3:GetObject even when
        // CloudFront (via OAC) can serve the object perfectly well.
        try {
            $bytes = strlen((string) Storage::disk($disk)->get($target));
            $bytes > 0
                ? $this->info("Verified {$bytes} bytes readable.")
                : $this->warn('Could not read the object back — verify the URL in a browser.');
        } catch (\Throwable) {
            $this->warn('Could not read the object back — verify the URL in a browser.');
        }

        return self::SUCCESS;
    }

    /**
     * S3 is unreachable — still set the logo so the site shows it. A file bundled
     * under public/ can be served directly by its root-relative path (e.g. /logo.png),
     * so the brand logo is never left unset just because the upload disk is down.
     */
    private function fallbackToLocal(string $path): int
    {
        $public = rtrim(public_path(), '/').'/';

        if (! str_starts_with($path, $public)) {
            $this->error('Source is outside public/, so there is no local URL to fall back to. Brand logo NOT changed.');

            return self::FAILURE;
        }

        $relative = '/'.ltrim(substr($path, strlen($public)), '/');
        SiteSetting::set('brand_logo', $relative);

        $this->warn("Set the brand logo to the bundled file {$relative} — served locally, NOT from S3.");
        $this->info('Live at: '.SiteSetting::brandLogoUrl());
        $this->line('Re-run once the S3 pipeline works to move it onto CloudFront.');

        return self::SUCCESS;
    }

    /** Turn a raw AWS exception into the one line that actually tells you what to change. */
    protected function explain(string $message): string
    {
        return match (true) {
            str_contains($message, 'SignatureDoesNotMatch') => 'AWS_SECRET_ACCESS_KEY does not match AWS_ACCESS_KEY_ID — check for stray whitespace or quotes in .env.',
            str_contains($message, 'InvalidAccessKeyId') => 'AWS_ACCESS_KEY_ID is not a valid key for this account.',
            str_contains($message, 'ExpiredToken') => 'The AWS session token has expired — refresh your credentials.',
            str_contains($message, 'AccessDenied') => 'Credentials are valid but lack s3:PutObject on this bucket.',
            str_contains($message, 'NoSuchBucket') => 'AWS_BUCKET does not exist in AWS_DEFAULT_REGION.',
            str_contains($message, 'PermanentRedirect') => 'Wrong AWS_DEFAULT_REGION for this bucket.',
            default => substr($message, 0, 200),
        };
    }
}
