<?php

use App\Http\Middleware\RedirectTrailingSlash;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Driven through the middleware directly rather than $this->get(): the test
// client trims trailing slashes off the URI (prepareUrlForRequest) before the
// request is built, so a request that ends in "/" cannot be made that way and
// the redirect would never be exercised.
function throughSlashMiddleware(string $method, string $uri): Response
{
    return (new RedirectTrailingSlash)->handle(
        Request::create($uri, $method),
        fn () => new Response('passed', 200),
    );
}

it('301s a trailing-slash path to the bare path', function () {
    $r = throughSlashMiddleware('GET', 'http://localhost/contact/');

    expect($r->getStatusCode())->toBe(301)
        ->and($r->headers->get('Location'))->toBe('http://localhost/contact');
});

it('301s HEAD too', function () {
    expect(throughSlashMiddleware('HEAD', 'http://localhost/contact/')->getStatusCode())->toBe(301);
});

it('preserves the query string', function () {
    $r = throughSlashMiddleware('GET', 'http://localhost/contact/?utm_source=x&a=1');

    expect($r->headers->get('Location'))->toBe('http://localhost/contact?utm_source=x&a=1');
});

it('collapses repeated trailing slashes', function () {
    expect(throughSlashMiddleware('GET', 'http://localhost/contact//')->headers->get('Location'))
        ->toBe('http://localhost/contact');
});

it('stays on the request host', function () {
    expect(throughSlashMiddleware('GET', 'https://thelastclicks.com/blog/')->headers->get('Location'))
        ->toBe('https://thelastclicks.com/blog');
});

it('leaves the home page alone', function () {
    expect(throughSlashMiddleware('GET', 'http://localhost/')->getStatusCode())->toBe(200);
});

it('leaves a path without a trailing slash alone', function () {
    expect(throughSlashMiddleware('GET', 'http://localhost/contact')->getStatusCode())->toBe(200);
});

it('never redirects a POST', function () {
    expect(throughSlashMiddleware('POST', 'http://localhost/contact/')->getStatusCode())->toBe(200);
});

it('does not touch admin, livewire or filament paths', function (string $path) {
    expect(throughSlashMiddleware('GET', 'http://localhost'.$path)->getStatusCode())->toBe(200);
})->with(['/admin/', '/admin/login/', '/livewire/update/', '/filament/x/']);
