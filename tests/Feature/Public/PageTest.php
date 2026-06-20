<?php

it('static pages return 200', function (string $path) {
    $this->get($path)->assertOk();
})->with([
    '/about',
    '/our-process',
    '/privacy-policy',
    '/terms-of-service',
    '/cookie-policy',
    '/disclaimer',
    '/thank-you',
]);
