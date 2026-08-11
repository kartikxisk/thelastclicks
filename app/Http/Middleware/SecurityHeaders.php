<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The response headers the site was serving none of.
 *
 * Scope check before anyone spends much time here: of these, only HTTPS is a
 * confirmed Google ranking signal, and it moves under ~1% of queries. These are
 * added because they are correct, not because they will move rankings.
 */
class SecurityHeaders
{
    /**
     * Content-Security-Policy, in Report-Only.
     *
     * Deliberately NOT enforcing. The policy below is a hypothesis, and shipping
     * an enforcing CSP on a site whose pages carry inline `<script
     * type="application/ld+json">`, `style="--ph-bg:url(…)"` attributes written
     * by Blade, YouTube embeds and CloudFront media does not degrade — it blanks
     * the page. Report-Only publishes the same policy, blocks nothing, and lets
     * real violations be collected first.
     *
     * To enforce later: watch the reports, close the gaps, then rename the
     * header to Content-Security-Policy. Do not simply flip it.
     *
     * 'unsafe-inline' on style-src is not laziness — Blade writes inline style
     * attributes to pass admin-managed image URLs into custom properties, and no
     * nonce mechanism covers attributes.
     */
    private const CSP = "default-src 'self'; "
        ."script-src 'self' 'unsafe-inline' https://www.youtube.com https://s.ytimg.com https://static.cloudflareinsights.com; "
        ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        ."font-src 'self' https://fonts.gstatic.com data:; "
        ."img-src 'self' data: https:; "
        ."media-src 'self' https:; "
        // www.google.com is the Google Maps embed on /contact. It was missing, and
        // Report-Only caught it exactly as intended — the studio map logged a
        // violation on every visit and would have vanished the moment this policy
        // was enforced. Closing the gap is the documented next step below, not
        // flipping the header.
        .'frame-src https://www.youtube.com https://www.youtube-nocookie.com https://www.google.com; '
        ."connect-src 'self'; "
        ."base-uri 'self'; "
        ."form-action 'self'; "
        ."frame-ancestors 'self'";
    // upgrade-insecure-requests is deliberately absent. Browsers IGNORE it in a
    // report-only policy and say so — it logged a console error on every page
    // load, which is noise in the one channel this policy exists to keep clean.
    // Add it back in the same commit that renames the header to enforcing.

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Stops a browser second-guessing a declared Content-Type — the sniffing
        // that turns an uploaded file served as text/plain into executable HTML.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Clickjacking. SAMEORIGIN rather than DENY: Filament renders previews in
        // same-origin frames, and DENY breaks them.
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Send the full URL to ourselves, origin-only to third parties. Keeps
        // analytics referrers intact without leaking paths off-site.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Features nothing here uses. Cheap to deny; costly to leave open.
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), interest-cohort=()');

        $response->headers->set('Content-Security-Policy-Report-Only', self::CSP);

        // HSTS only over HTTPS. Sending it on a plaintext response is meaningless
        // (the browser ignores it), and in local dev over http it would be a lie.
        //
        // includeSubDomains and preload are both required for preload-list
        // submission. Know what preload means before deploying this: it is a
        // one-way door — browsers ship the entry, and removal takes months. Every
        // present and future subdomain must serve valid HTTPS from that point on.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
