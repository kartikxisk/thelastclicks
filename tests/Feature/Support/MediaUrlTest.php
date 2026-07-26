<?php

use App\Support\MediaUrl;
use Illuminate\Support\Facades\Storage;

it('returns null for anything blank', function (?string $value) {
    expect(MediaUrl::resolve($value))->toBeNull();
})->with([null, '', '   ']);

it('passes an already-formed URL straight through', function (string $url) {
    expect(MediaUrl::resolve($url))->toBe($url)
        // Even when a disk is supplied — a pasted CDN URL is never a disk key.
        ->and(MediaUrl::onMediaDisk($url))->toBe($url);
})->with([
    'https://images.unsplash.com/photo-1.jpg',
    'http://example.com/a.png',
    '//cdn.example.com/b.png',
    'data:image/gif;base64,R0lGOD',
]);

it('resolves a bundled public file to a full URL', function () {
    expect(MediaUrl::asset('clients/bmw.png'))->toBe(asset('clients/bmw.png'));
});

it('treats a leading slash as a public file, not a disk key', function () {
    // og:image requires an absolute URL, so this must not pass through verbatim.
    expect(MediaUrl::onMediaDisk('/clients/bmw.png'))->toBe(asset('clients/bmw.png'))
        ->and(MediaUrl::asset('/clients/bmw.png'))->toBe(asset('clients/bmw.png'));
});

it('resolves a relative path against the disk base URL, without touching the driver', function () {
    // Built by string from the disk's configured base (CloudFront for s3) rather
    // than Storage::disk()->url(), so displaying media never instantiates the s3
    // driver — which crashes on servers with an old flysystem-aws-s3-v3.
    config([
        'media-library.disk_name' => 's3',
        'filesystems.disks.s3.url' => 'https://cdn.test',
    ]);

    expect(MediaUrl::onMediaDisk('branding/logo.png'))->toBe('https://cdn.test/branding/logo.png');
});

it('falls back to the driver only when the disk has no base URL', function () {
    Storage::fake('local');
    config([
        'media-library.disk_name' => 'local',
        'filesystems.disks.local.url' => null,
    ]);

    expect(MediaUrl::onMediaDisk('branding/logo.png'))
        ->toBe(Storage::disk('local')->url('branding/logo.png'));
});

it('trims surrounding whitespace before resolving', function () {
    expect(MediaUrl::resolve('  clients/bmw.png  '))->toBe(asset('clients/bmw.png'));
});

it('knows which values are already URLs', function () {
    expect(MediaUrl::isAbsolute('https://a.test/x.png'))->toBeTrue()
        ->and(MediaUrl::isAbsolute('//a.test/x.png'))->toBeTrue()
        ->and(MediaUrl::isAbsolute('clients/x.png'))->toBeFalse()
        ->and(MediaUrl::isAbsolute('/clients/x.png'))->toBeFalse();
});
