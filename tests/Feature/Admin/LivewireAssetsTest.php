<?php

/**
 * Livewire's JS must reach the browser as a real file under public/.
 *
 * Production nginx serves *.js from a static-file location whose miss path is
 * `error_page 404 -> /index.php`, and an error_page internal redirect KEEPS the
 * 404 status. Livewire's own `/livewire/livewire.min.js` route is PHP, not a
 * file, so it came back with the correct JS body under a 404 — which a browser
 * refuses to execute. Livewire never booted, Filament's login form
 * (`<form method="post" wire:submit="authenticate">`, no action attribute) fell
 * back to a native POST to /admin/login, and the panel answered every login
 * attempt with "405 Method Not Allowed".
 *
 * `php artisan livewire:publish --assets` is what puts the file there; it runs
 * on every deploy. These tests fail if that copy goes missing or goes stale.
 */
it('publishes Livewire assets into public/', function () {
    expect(public_path('vendor/livewire/manifest.json'))->toBeFile();
    expect(public_path('vendor/livewire/livewire.min.js'))->toBeFile();
    expect(public_path('vendor/livewire/livewire.js'))->toBeFile();
});

it('keeps the published Livewire assets in step with the installed version', function () {
    // Livewire compares these two manifests at render time and only console.warn()s
    // on a mismatch — a warning nobody reads. Failing here instead is the point:
    // a Livewire upgrade that skips the re-publish ships JS from the old version.
    $published = public_path('vendor/livewire/manifest.json');
    $installed = base_path('vendor/livewire/livewire/dist/manifest.json');

    expect(json_decode(file_get_contents($published), true))
        ->toBe(json_decode(file_get_contents($installed), true));
});

it('points the admin login page at the published file, not the PHP route', function () {
    $html = $this->get('/admin/login')->assertOk()->getContent();

    // The route form is what 404s behind nginx. Anything under /vendor/livewire/
    // is a real file the web server can serve itself.
    expect($html)->not->toContain('src="'.url('/livewire/livewire'));
    expect($html)->toContain(url('/vendor/livewire/livewire'));
});
