<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use App\Support\LogoImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportBrandLogo extends Command
{
    protected $signature = 'brand:import-logo
        {path? : Logo file to upload (defaults to public/logo.png)}
        {--variant=light : "light" for the mark used on dark backgrounds, "dark" for the black mark used on light ones}
        {--max=1200 : Longest edge, in pixels, after trimming}
        {--raw : Skip the trim/resize and upload the file exactly as supplied}
        {--public= : Also write the processed file here, relative to public/ (e.g. logo.png)}';

    protected $description = 'Trim, compress and upload a logo, then set it as the light or dark brand mark';

    public function handle(): int
    {
        $variant = (string) $this->option('variant');

        if (! in_array($variant, ['light', 'dark'], true)) {
            $this->error("--variant must be 'light' or 'dark', got '{$variant}'.");

            return self::FAILURE;
        }

        // Two settings, two backgrounds. brand_logo is the mark the public site
        // uses (everything out there is --ink); brand_logo_dark is the black mark
        // for light surfaces, currently the admin panel in light mode.
        $setting = $variant === 'light' ? 'brand_logo' : 'brand_logo_dark';

        $path = $this->argument('path') ?: public_path('logo.png');

        if (! is_file($path)) {
            $this->error("No file at {$path}");

            return self::FAILURE;
        }

        if (! $this->option('raw')) {
            $path = $this->prepare($path);

            if ($path === null) {
                return self::FAILURE;
            }
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            $this->error("Could not read {$path}");

            return self::FAILURE;
        }

        if ($target = $this->option('public')) {
            $this->writePublicCopy($contents, (string) $target);
        }

        $disk = config('filament.default_filesystem_disk', config('filesystems.default'));
        // Content-hash the key so a changed logo is a NEW URL. CloudFront serves media
        // with a 1-year immutable Cache-Control, so overwriting a fixed key (logo.png)
        // would keep serving the stale object at the edge. Identical content → identical
        // key → idempotent; new content → new key → no stale cache.
        $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';
        // The variant is in the key so the two marks cannot collide, and so the
        // object is identifiable in the bucket without opening it.
        $prefix = $variant === 'light' ? 'logo' : 'logo-dark';
        $target = 'branding/'.$prefix.'-'.substr(md5($contents), 0, 10).'.'.$ext;

        // Never assume success — a bad path here would point the whole site at a broken
        // image. Handles both failure modes: an exception ('throw' => true) and a false
        // return (if AWS_THROW is turned off).
        try {
            $written = Storage::disk($disk)->put($target, $contents);
        } catch (\Throwable $e) {
            $this->error("Upload to disk [{$disk}] failed: ".$this->explain($e->getMessage()));

            return $this->fallbackToLocal($path, $setting);
        }

        if ($written === false) {
            $this->error("Disk [{$disk}] refused the write (credentials / bucket permissions).");

            return $this->fallbackToLocal($path, $setting);
        }

        SiteSetting::set($setting, $target);

        $this->info("Uploaded to [{$disk}] {$target}");
        $this->info("Set [{$setting}] — the {$variant} mark.");
        $this->info('Live at: '.($variant === 'light' ? SiteSetting::brandLogoUrl() : SiteSetting::brandLogoDarkUrl()));

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
     * Trim the empty margin off the supplied file and scale it for the web.
     *
     * Design exports arrive as big squares with the mark floating in the middle;
     * shipped untrimmed, every place that sizes the logo by height sizes the
     * PADDING instead, and the visible mark comes out a fraction of the intended
     * size. Returns the temp path of the processed file, or null on failure.
     */
    private function prepare(string $path): ?string
    {
        $out = tempnam(sys_get_temp_dir(), 'brandlogo').'.png';

        try {
            $before = (int) filesize($path);
            $result = LogoImage::process($path, $out, (int) $this->option('max'));
        } catch (\Throwable $e) {
            $this->error('Could not process the image: '.$e->getMessage());
            $this->line('Re-run with --raw to upload it untouched.');

            return null;
        }

        $box = $result['trimmed'];
        $this->info(sprintf(
            'Trimmed to %d,%d–%d,%d then scaled to %d×%d. %s → %s (%d%% smaller).',
            $box['left'], $box['top'], $box['right'], $box['bottom'],
            $result['width'], $result['height'],
            $this->humanBytes($before), $this->humanBytes($result['bytes']),
            $before > 0 ? (int) round((1 - $result['bytes'] / $before) * 100) : 0,
        ));

        return $out;
    }

    /**
     * Keep a copy under public/ as well. It is what `brand:import-logo` reads by
     * default on a fresh checkout, and what the local fallback above can serve if
     * S3 is unreachable.
     */
    private function writePublicCopy(string $contents, string $target): void
    {
        $dest = public_path(ltrim($target, '/'));

        if (! is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0775, true);
        }

        file_put_contents($dest, $contents) === false
            ? $this->warn("Could not write {$dest}")
            : $this->info("Wrote {$dest} (".$this->humanBytes(strlen($contents)).').');
    }

    private function humanBytes(int $bytes): string
    {
        return $bytes >= 1048576
            ? round($bytes / 1048576, 2).' MB'
            : round($bytes / 1024).' KB';
    }

    /**
     * S3 is unreachable — still set the logo so the site shows it. A file bundled
     * under public/ can be served directly by its root-relative path (e.g. /logo.png),
     * so the brand logo is never left unset just because the upload disk is down.
     */
    private function fallbackToLocal(string $path, string $setting = 'brand_logo'): int
    {
        $public = rtrim(public_path(), '/').'/';

        if (! str_starts_with($path, $public)) {
            $this->error('Source is outside public/, so there is no local URL to fall back to. Brand logo NOT changed.');

            return self::FAILURE;
        }

        $relative = '/'.ltrim(substr($path, strlen($public)), '/');
        SiteSetting::set($setting, $relative);

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
