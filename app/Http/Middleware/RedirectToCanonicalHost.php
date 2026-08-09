<?php

namespace App\Http\Middleware;

use App\Support\AppUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends every request to the host APP_URL declares.
 *
 * www.thelastclicks.com and thelastclicks.com both answered 200, each serving a
 * self-referencing canonical pointing at itself. That is two complete copies of
 * the site competing for the same rankings, with links and authority split
 * between them and no signal telling Google which to keep.
 *
 * A 301 at the CDN would also fix it, and belongs there for the latency win —
 * but CDN config is not in this repository, cannot be reviewed in a pull
 * request, and silently stops applying the day the site moves. This makes the
 * app correct on its own; the CDN rule becomes an optimisation rather than the
 * only thing standing between the site and duplicate hosts.
 */
class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $target = parse_url(AppUrl::current(), PHP_URL_HOST);

        // No usable APP_URL host, or it points at localhost: do nothing. Local
        // and CI hit the app on 127.0.0.1, ::1 and .test hosts, and redirecting
        // those to a configured production domain would break every dev machine
        // and every feature test.
        if (! is_string($target) || $target === '' || AppUrl::isLocal()) {
            return $next($request);
        }

        if (strcasecmp($request->getHost(), $target) === 0) {
            return $next($request);
        }

        // GET/HEAD only. A 301 on a POST is not safe to follow — the method and
        // body are allowed to be dropped, so a redirected quote submission would
        // arrive empty or not at all. A form posted to the wrong host is a
        // configuration error to fix, not one to paper over.
        if (! $request->isMethodCacheable()) {
            return $next($request);
        }

        $scheme = AppUrl::isSecure() ? 'https' : $request->getScheme();

        return redirect()->away(
            $scheme.'://'.$target.$request->getRequestUri(),
            301
        );
    }
}
