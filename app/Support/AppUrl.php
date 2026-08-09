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

    /**
     * An absolute canonical URL built on APP_URL's host, whatever host the
     * request arrived on.
     *
     * Both `url()->current()` and `url('/about')` derive their host from the
     * incoming request, so www.example.com and example.com each served a
     * self-referencing canonical pointing at themselves — two complete copies of
     * the site, each claiming to be the original. The host is not the visitor's
     * to choose; it is configuration.
     *
     * Accepts a path, an absolute URL, or null for the current request. Passing
     * an absolute URL is the important case: pages pass `url('/about')` into the
     * layout, and that value is already wrong by the time it arrives — so this
     * takes the path off it and rebuilds on the configured host rather than
     * trusting it.
     *
     * Path and query are preserved; only scheme and host are pinned.
     */
    public static function canonical(?string $pathOrUrl = null): string
    {
        $base = rtrim(self::current(), '/');

        // Full path INCLUDING the query string: paginated and filtered URLs are
        // distinct pages and must not all collapse onto page one.
        $value = $pathOrUrl ?? request()->getRequestUri();

        if (preg_match('~^https?://~i', $value) === 1) {
            $path = (string) parse_url($value, PHP_URL_PATH);
            $query = parse_url($value, PHP_URL_QUERY);
            $value = $path.(is_string($query) && $query !== '' ? '?'.$query : '');
        }

        $value = '/'.ltrim($value, '/');

        // Trailing slashes stripped so /about and /about/ cannot both be emitted
        // as canonical from different call sites.
        return $value === '/' ? $base : $base.rtrim($value, '/');
    }
}
