<?php

use Illuminate\Support\Facades\Storage;

it('test suite pins the medialibrary disk to public', function () {
    // phpunit.xml pins MEDIA_DISK=public for the suite
    expect(config('media-library.disk_name'))->toBe('public');
});

it('medialibrary sends immutable cache headers for remote disks', function () {
    expect(config('media-library.remote.extra_headers.CacheControl'))
        ->toBe('max-age=31536000, immutable');
});

it('s3 urls resolve through the configured CloudFront domain', function () {
    config([
        'filesystems.disks.s3.url' => 'https://cdn.example.com',
        'filesystems.disks.s3.bucket' => 'bucket',
        'filesystems.disks.s3.region' => 'us-east-1',
        'filesystems.disks.s3.key' => 'k',
        'filesystems.disks.s3.secret' => 's',
    ]);

    expect(Storage::disk('s3')->url('media/1/film.mp4'))
        ->toBe('https://cdn.example.com/media/1/film.mp4');
});

it('gives billing its own disk, defaulted somewhere CloudFront has no origin for', function () {
    // The signature and the stamp live here rather than on MEDIA_DISK. That disk
    // is an s3 bucket with CloudFront in front of it, serving anonymously —
    // ->visibility('private') sets an object ACL and does not change what the
    // CDN hands out. The default has to be a path with no CDN origin at all.
    $disk = config('filesystems.disks.billing_private');

    expect($disk)->toBeArray()
        ->and($disk['driver'])->toBe('local')
        ->and($disk['root'])->toStartWith(storage_path())
        // Never public: `public/storage` is symlinked into the docroot, and an
        // object on the media bucket is a CloudFront URL away from anyone.
        ->and($disk['root'])->not->toStartWith(storage_path('app/public'))
        ->and($disk)->not->toHaveKey('url');
});

it('keeps the billing disk distinct from the publicly-served media disk', function () {
    expect(config('media-library.disk_name'))->not->toBe('billing_private');
});
