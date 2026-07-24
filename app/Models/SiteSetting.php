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
}
