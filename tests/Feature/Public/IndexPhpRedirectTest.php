<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('301s /index.php to the root', function () {
    // /index.php served the whole home page at 200 with a canonical pointing at
    // itself — a second indexable copy of the most important page on the site.
    $response = $this->get('/index.php');
    $response->assertStatus(301);

    // The Location header is asserted literally rather than through
    // assertRedirect('/'), which compares against url('/') — and url() builds on
    // the request's base URL, which on this very request IS /index.php. So the
    // loop this middleware shipped with (301 /index.php -> /index.php) passed
    // assertRedirect('/') and only showed up in a browser.
    expect($response->headers->get('Location'))->not->toContain('index.php');
});

it('keeps the query string when dropping /index.php', function () {
    $location = (string) $this->get('/index.php?utm_source=qa')->assertStatus(301)->headers->get('Location');

    expect($location)->not->toContain('index.php')->and($location)->toEndWith('/?utm_source=qa');
});

it('301s the PATH_INFO form to the clean path', function () {
    $location = (string) $this->get('/index.php/about')->assertStatus(301)->headers->get('Location');

    expect($location)->not->toContain('index.php')->and($location)->toEndWith('/about');
});

it('leaves an ordinary path alone', function () {
    $this->get('/about')->assertOk();
});
