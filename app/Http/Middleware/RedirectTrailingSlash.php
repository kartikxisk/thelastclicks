<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collapses `/contact/` onto `/contact` with a 301.
 *
 * Laravel's router matches both spellings, so every page answered 200 at two
 * URLs and the canonical tag was the only thing telling crawlers they were one
 * page. This turns the second URL into a redirect so it is never indexable,
 * never cached, and never collects inbound links of its own.
 *
 * Runs in the global stack ahead of the route group's cacheResponse, so the
 * response cache never stores the slash variant.
 */
class RedirectTrailingSlash
{
    /**
     * Prefixes that own their URL shape. Filament and Livewire generate and
     * route their own paths (Livewire's update endpoint, Filament's panel
     * pages); redirecting inside them risks a loop or a broken component call.
     */
    private const SKIP_PREFIXES = ['admin', 'livewire', 'filament'];

    public function handle(Request $request, Closure $next): Response
    {
        // GET/HEAD only, for the reason RedirectToCanonicalHost spells out: a
        // 301 may drop the method and body, so a redirected POST arrives empty.
        if (! $request->isMethodCacheable()) {
            return $next($request);
        }

        // Raw REQUEST_URI path, as RedirectIndexPhp does: Symfony's pathInfo
        // is already normalised and would hide the trailing slash from us.
        $path = (string) parse_url((string) $request->server('REQUEST_URI', ''), PHP_URL_PATH);

        if ($path === '' || $path === '/' || ! str_ends_with($path, '/')) {
            return $next($request);
        }

        $clean = rtrim($path, '/');

        // A path of only slashes ("//") collapses to the home page, not to "".
        if ($clean === '') {
            $clean = '/';
        }

        $first = strtolower(explode('/', ltrim($clean, '/'))[0]);
        if (in_array($first, self::SKIP_PREFIXES, true)) {
            return $next($request);
        }

        // Raw query, not getQueryString(): that re-sorts the parameters, which
        // would rewrite campaign URLs a tracker may match byte-for-byte.
        $query = parse_url((string) $request->server('REQUEST_URI', ''), PHP_URL_QUERY);
        $target = $clean.(is_string($query) && $query !== '' ? '?'.$query : '');

        // away() with the host built by hand, not redirect($target): the URL
        // generator prefixes the request's base URL, which RedirectIndexPhp
        // documents as a way to redirect to the very URL being fixed.
        return redirect()->away($request->getSchemeAndHttpHost().$target, 301);
    }
}
