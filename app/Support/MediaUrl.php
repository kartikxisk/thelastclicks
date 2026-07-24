<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Turns a stored image reference into a URL the browser can load.
 *
 * The same three rules were reimplemented (slightly differently, and in one case
 * incompletely) on Client, SiteSetting and SeoPage. They live here now:
 *
 *   1. blank              → null, so callers can fall back
 *   2. already a URL      → passed through untouched
 *   3. anything else      → a path resolved on a storage disk, or on public/
 *
 * Rule 2 matters everywhere: every one of these fields can legitimately hold a
 * pasted CDN/Unsplash URL instead of an upload.
 */
final class MediaUrl
{
    /**
     * @param  string|null  $disk  Storage disk to resolve a relative path on.
     *                             Null resolves it as a file bundled under public/.
     */
    public static function resolve(?string $path, ?string $disk = null): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (self::isAbsolute($path)) {
            return $path;
        }

        // A leading slash means a file served from public/, never a disk key —
        // neither Filament nor media library stores keys that way. Resolved to a
        // full URL rather than passed through, because og:image needs absolute.
        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return $disk === null
            ? asset($path)
            : Storage::disk($disk)->url($path);
    }

    /** Uploads handled by media library (S3 via MEDIA_DISK). */
    public static function onMediaDisk(?string $path): ?string
    {
        return self::resolve($path, config('media-library.disk_name', 'public'));
    }

    /** Uploads made through a Filament form field. */
    public static function onUploadDisk(?string $path): ?string
    {
        return self::resolve($path, config('filament.default_filesystem_disk', 'public'));
    }

    /** A file shipped in the repo under public/. */
    public static function asset(?string $path): ?string
    {
        return self::resolve($path);
    }

    /** Already a fully-formed URL (or a data/protocol-relative one)? */
    public static function isAbsolute(string $path): bool
    {
        return (bool) preg_match('~^(https?://|//|data:)~i', $path);
    }
}
