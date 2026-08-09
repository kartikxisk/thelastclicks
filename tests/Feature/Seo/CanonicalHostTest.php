<?php

use App\Http\Middleware\RedirectToCanonicalHost;
use App\Models\SeoPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * The duplicate-host bug these cover: www.thelastclicks.com and
 * thelastclicks.com both answered 200 and each emitted a canonical pointing at
 * itself, so Google saw two complete copies of the site competing for the same
 * rankings.
 *
 * Absolute URLs rather than a Host header — the test client derives the request
 * host from the URL it is given, and a bare Host header does not reliably
 * override it.
 */
it('pins the canonical to the APP_URL host, whatever host the request arrives on', function () {
    config(['app.url' => 'https://thelastclicks.com']);

    // Middleware disabled on purpose: this asserts the canonical TAG is correct
    // even for a request the redirect would normally have turned away. The two
    // defences are independent, and this is the one that still matters if the
    // redirect is ever handled upstream at the CDN instead.
    $html = $this->withoutMiddleware(RedirectToCanonicalHost::class)
        ->get('https://www.thelastclicks.com/about')
        ->assertOk()
        ->getContent();

    // Scoped to the canonical tag, not the whole page: url() still renders nav and
    // footer links on the request host, which is standard Laravel behaviour and
    // harmless — the redirect above means no visitor is on that host to follow
    // them. The canonical is the tag that must not drift.
    expect($html)->toContain('<link rel="canonical" href="https://thelastclicks.com/about">')
        ->and($html)->not->toContain('rel="canonical" href="https://www.');
});

it('pins og:url to the same host as the canonical', function () {
    config(['app.url' => 'https://thelastclicks.com']);

    $html = $this->withoutMiddleware(RedirectToCanonicalHost::class)
        ->get('https://www.thelastclicks.com/about')
        ->getContent();

    expect($html)->toContain('<meta property="og:url" content="https://thelastclicks.com/about">');
});

it('redirects a wrong-host GET to the canonical host, preserving path and query', function () {
    config(['app.url' => 'https://thelastclicks.com']);

    $this->get('https://www.thelastclicks.com/about?utm_source=x')
        ->assertStatus(301)
        ->assertRedirect('https://thelastclicks.com/about?utm_source=x');
});

it('leaves a request already on the canonical host alone', function () {
    config(['app.url' => 'https://thelastclicks.com']);

    $this->get('https://thelastclicks.com/about')->assertOk();
});

/**
 * A 301 is allowed to drop the method and body, so redirecting a POST would
 * deliver an empty submission — worse than failing loudly.
 */
it('never redirects a POST across hosts', function () {
    config(['app.url' => 'https://thelastclicks.com']);

    $status = $this->post('https://www.thelastclicks.com/newsletter', ['email' => 'x@example.com'])
        ->getStatusCode();

    expect($status)->not->toBe(301);
});

/**
 * Local and CI reach the app on 127.0.0.1 and .test hosts. Redirecting those at
 * a configured production domain would break every dev machine.
 */
it('does not redirect when APP_URL is local', function () {
    config(['app.url' => 'http://localhost']);

    $this->get('http://anything.test/about')->assertOk();
});

/**
 * A cross-domain canonical set in Manage SEO is a deliberate choice — syndicated
 * copy pointing back at the original. Normalising it onto our own host would
 * silently defeat the reason it was set.
 */
it('leaves an admin-set canonical override exactly as typed', function () {
    config(['app.url' => 'https://thelastclicks.com']);

    SeoPage::query()->create([
        'page_url' => '/about',
        'label' => 'About',
        'canonical_url' => 'https://syndicated.example.com/original',
        'is_active' => true,
    ]);

    $this->get('https://thelastclicks.com/about')
        ->assertOk()
        ->assertSee('rel="canonical" href="https://syndicated.example.com/original"', false);
});
