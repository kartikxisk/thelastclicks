<?php

namespace App\Support;

/**
 * APP_URL drives canonical tags, og:url, sitemap <loc> entries and every
 * asset() call. Shipping a local value to production silently points the whole
 * site at a host crawlers cannot reach, so both the sitemap generator and the
 * deploy preflight ask this one question the same way.
 */
final class AppUrl
{
    /** Hosts that only ever mean "not a public site". */
    private const LOCAL_MARKERS = ['localhost', '127.0.0.1', '0.0.0.0', '::1', '.test', '.local', '.localhost'];

    public static function current(): string
    {
        return (string) config('app.url');
    }

    public static function isLocal(?string $url = null): bool
    {
        $url = strtolower($url ?? self::current());

        foreach (self::LOCAL_MARKERS as $marker) {
            if (str_contains($url, $marker)) {
                return true;
            }
        }

        return false;
    }

    public static function isSecure(?string $url = null): bool
    {
        return str_starts_with(strtolower($url ?? self::current()), 'https://');
    }
}
