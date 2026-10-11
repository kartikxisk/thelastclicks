<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Removes the front controller from the URL with a 301.
 *
 * `/index.php` answered 200 with the whole home page and a canonical pointing
 * at itself, so the site had a second indexable copy of its most important page
 * — the duplicate-host problem RedirectToCanonicalHost solves, one level down.
 * Laravel cannot fix this in a route: Symfony strips the script name before the
 * router ever sees the path, so `/index.php` arrives as `/` and the home route
 * serves it. Only the raw REQUEST_URI still remembers, which is what this
 * reads.
 *
 * The canonical tag is derived from that same REQUEST_URI
 * (App\Support\AppUrl::canonical), so this redirect is also what stops the
 * wrong canonical being emitted, rather than patching the tag and leaving two
 * live URLs.
 */
class RedirectIndexPhp
{
    public function handle(Request $request, Closure $next): Response
    {
        // GET/HEAD only, for the reason RedirectToCanonicalHost spells out: a
        // 301 is allowed to drop the method and body, so a redirected POST can
        // arrive empty.
        if (! $request->isMethodCacheable()) {
            return $next($request);
        }

        $path = (string) parse_url((string) $request->server('REQUEST_URI', ''), PHP_URL_PATH);

        if (preg_match('~(?:^|/)index\.php(?:$|/)~i', $path) !== 1) {
            return $next($request);
        }

        // PATH_INFO form too: /index.php/about is the same page as /about.
        $clean = (string) preg_replace('~(?:^|/)index\.php~i', '', $path);
        $clean = '/'.ltrim($clean, '/');

        $query = $request->getQueryString();
        $target = $clean.(is_string($query) && $query !== '' ? '?'.$query : '');

        // away(), with the host assembled by hand — NOT redirect($target).
        // Laravel's URL generator builds every relative path on
        // getSchemeAndHttpHost() . getBaseUrl(), and on a request that arrived
        // as /index.php the base URL *is* /index.php. So redirect('/') answered
        // with http://host/index.php: a 301 pointing at the URL it was sent to
        // fix, which browsers follow exactly once and then give up on.
        // getSchemeAndHttpHost() has no base URL in it.
        return redirect()->away($request->getSchemeAndHttpHost().$target, 301);
    }
}
