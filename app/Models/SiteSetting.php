<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value_json'];

    protected $casts = ['value_json' => 'array'];

    /**
     * Aspect ratios offered for the homepage portfolio tiles. Keys are valid CSS
     * `aspect-ratio` values and go straight into a custom property, so anything
     * accepted here must be safe to emit — see the guard in SiteSettingsPage.
     *
     * @var array<string, string>
     */
    public const WORK_TILE_RATIOS = [
        '16 / 9' => 'Widescreen 16:9 — film stills',
        '3 / 2'  => 'Classic 3:2 — stills photography',
        '4 / 3'  => 'Standard 4:3 — mixed material',
        '1 / 1'  => 'Square 1:1 — safest crop',
        '4 / 5'  => 'Portrait 4:5 — social',
        '9 / 16' => 'Vertical 9:16 — reels',
    ];

    public const DEFAULT_WORK_TILE_RATIO = '4 / 3';

    /** Bundled clip used when no CTA background video has been uploaded. */
    public const DEFAULT_CTA_VIDEO = '/videos/bg-footer.mp4';

    /** Background video for the closing CTA band. */
    public static function ctaVideoUrl(): string
    {
        $path = static::get('cta_video');

        return (is_string($path) && $path !== '')
            ? (MediaUrl::onUploadDisk($path) ?: self::DEFAULT_CTA_VIDEO)
            : self::DEFAULT_CTA_VIDEO;
    }

    /** The configured tile ratio, falling back when unset or unrecognised. */
    public static function workTileRatio(): string
    {
        $v = (string) static::get('work_tile_ratio', self::DEFAULT_WORK_TILE_RATIO);

        return isset(self::WORK_TILE_RATIOS[$v]) ? $v : self::DEFAULT_WORK_TILE_RATIO;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $row = static::find($key);
        } catch (QueryException) {
            return $default;
        }
        if (! $row) {
            return $default;
        }
        $v = $row->value_json;

        return is_array($v) && array_key_exists('v', $v) ? $v['v'] : $v;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value_json' => ['v' => $value]]);
    }

    /**
     * Brand logo URL for the nav, preloader, quote modal and og:image.
     * Admin uploads land on the Filament disk (S3) as a path; anything already
     * absolute is passed through. Returns null when nothing is uploaded — callers
     * must render no logo at all rather than substituting a bundled file.
     */
    public static function brandLogoUrl(): ?string
    {
        $path = static::get('brand_logo');

        return is_string($path) ? MediaUrl::onUploadDisk($path) : null;
    }

    /**
     * Favicon URL, also used as the Apple touch icon.
     *
     * Unlike the brand logo this always resolves: an absent favicon just gets the
     * browser's blank default, so the bundled file is a better floor than nothing.
     */
    public static function faviconUrl(): string
    {
        $path = static::get('favicon');

        return (is_string($path) ? MediaUrl::onUploadDisk($path) : null) ?: asset('favicon.png');
    }

    /**
     * Admin-managed page image (page-header backgrounds, the About studio photo).
     * Uploaded to S3; stored as a key and resolved to the CloudFront host at
     * runtime. Null when nothing is uploaded — callers render no image.
     */
    public static function pageImage(string $key): ?string
    {
        $path = static::get("page_image_{$key}");

        return is_string($path) ? MediaUrl::onUploadDisk($path) : null;
    }
}
