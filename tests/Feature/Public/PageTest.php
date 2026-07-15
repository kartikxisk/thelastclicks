<?php

it('static pages return 200', function (string $path) {
    $this->get($path)->assertOk();
})->with([
    '/about',
    '/privacy-policy',
    '/terms-of-service',
    '/cookie-policy',
    '/disclaimer',
    '/thank-you',
]);

it('our-process permanently redirects to about', function () {
    $this->get('/our-process')->assertRedirect('/about')->assertStatus(301);
});
