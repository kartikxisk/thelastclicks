<?php

it('sentry config publishes', function () {
    expect(file_exists(config_path('sentry.php')))->toBeTrue();
});

it('sentry DSN env var is recognised', function () {
    config(['sentry.dsn' => 'https://example@sentry.io/123']);
    expect(config('sentry.dsn'))->toBe('https://example@sentry.io/123');
});
