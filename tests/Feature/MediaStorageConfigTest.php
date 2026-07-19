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
