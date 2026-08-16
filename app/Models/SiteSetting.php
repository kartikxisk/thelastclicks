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
        '3 / 2' => 'Classic 3:2 — stills photography',
        '4 / 3' => 'Standard 4:3 — mixed material',
        '1 / 1' => 'Square 1:1 — safest crop',
        '4 / 5' => 'Portrait 4:5 — social',
        '9 / 16' => 'Vertical 9:16 — reels',
    ];

    /**
     * Vertical, to match the homepage strip the tiles actually live in.
     *
     * This is a code default rather than a seeded row on purpose: seeding it
     * would overwrite an editor's choice on every deploy, the same trap
     * WorksSeeder avoids. An environment that has never touched the setting
     * gets 9:16; one where somebody picked a ratio in Site Settings keeps it.
     *
     * It was 4 / 3, which is why production rendered wide tiles while local —
     * where the setting had been changed by hand — rendered vertical ones.
     */
    public const DEFAULT_WORK_TILE_RATIO = '9 / 16';

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

    /**
     * Meta Pixel ID, or null unless it is a plausible one.
     *
     * The value is interpolated into an inline <script>, so it is pattern-checked
     * rather than trusted — the same reasoning as WORK_TILE_RATIOS above, with a
     * worse failure mode: a pasted apostrophe breaks every script on the page,
     * and a deliberate one is stored XSS with an admin as the author. Meta issues
     * these as digits only, so anything else is a paste accident at best.
     */
    public static function metaPixelId(): ?string
    {
        $id = trim((string) static::get('meta_pixel_id'));

        return preg_match('/^\d{6,20}$/', $id) === 1 ? $id : null;
    }

    /**
     * GA4 measurement ID, or null unless it looks like one.
     *
     * Same escaping argument as metaPixelId(). GA4 IDs are "G-" plus an
     * alphanumeric token; a Universal Analytics "UA-…" ID is deliberately
     * rejected because those properties stopped collecting in 2024 and silently
     * firing at one looks like working analytics while recording nothing.
     */
    public static function gaMeasurementId(): ?string
    {
        $id = strtoupper(trim((string) static::get('ga_measurement_id')));

        return preg_match('/^G-[A-Z0-9]{4,20}$/', $id) === 1 ? $id : null;
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
     * Fallback share image, resolved to something a crawler can actually fetch.
     *
     * The stored value is an upload key ('headers/gear-camera-dark.jpg'), and the
     * layout was emitting it raw — so every og:image and twitter:image on the
     * site carried a relative path. Social platforms and crawlers fetch that URL
     * from their own servers, where a relative path resolves against nothing, so
     * the share card silently had no image anywhere. MediaUrl also passes a
     * pasted absolute URL through untouched, which is the other thing this field
     * legitimately holds.
     */
    public static function defaultOgImageUrl(): ?string
    {
        $path = static::get('seo_default_og_image');

        return is_string($path) ? MediaUrl::onUploadDisk($path) : null;
    }

    /**
     * The dark-ink logo, for light backgrounds.
     *
     * brand_logo is the light-on-transparent mark the public site needs — every
     * surface out there is --ink. This is the same mark drawn in black, for the
     * places that are NOT dark: the admin panel in light mode, and anything
     * printed or embedded on white.
     *
     * Falls back to brand_logo rather than to null. One logo showing in the wrong
     * colour is recoverable; the admin panel losing its logo entirely because only
     * one variant was uploaded is a worse failure, and the fallback means a studio
     * that only ever uploads one file still gets a working site.
     */
    public static function brandLogoDarkUrl(): ?string
    {
        $path = static::get('brand_logo_dark');

        return (is_string($path) ? MediaUrl::onUploadDisk($path) : null)
            ?: static::brandLogoUrl();
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
