<?php

use App\Models\Client;
use App\Models\Industry;
use App\Models\SeoPage;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * The orphan sweep deletes from the media disk, so every test here is really
 * asking the same question: can it delete something that is still in use?
 *
 * The keep-set is derived from live rows rather than from a list of prefixes to
 * remove, which is what makes that answer no — a reference the sweep does not
 * understand yet survives, instead of being destroyed by omission.
 */
beforeEach(function () {
    $this->seed();
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
});

function prune(array $opts = []): int
{
    return Artisan::call('media:prune-orphans', array_merge(['--force' => true], $opts));
}

it('deletes an object no row points at', function () {
    Storage::disk('s3')->put('portfolio/retired-campaign.jpg', 'bytes');

    prune();

    Storage::disk('s3')->assertMissing('portfolio/retired-campaign.jpg');
});

it('keeps a file medialibrary still owns', function () {
    $work = Work::factory()->create();
    $work->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('cover');

    $path = $work->getFirstMedia('cover')->id.'/cover.jpg';
    Storage::disk('s3')->assertExists($path);

    prune();

    Storage::disk('s3')->assertExists($path);
});

it('keeps site chrome even when nothing references it', function () {
    // The logo, favicon and page headers. Losing these breaks every page at
    // once, so they are pinned regardless of what the database says.
    foreach (['branding/logo.png', 'headers/about.jpg', 'logo.png', 'industries/alcobev.jpg'] as $key) {
        Storage::disk('s3')->put($key, 'bytes');
    }

    prune();

    foreach (['branding/logo.png', 'headers/about.jpg', 'logo.png', 'industries/alcobev.jpg'] as $key) {
        Storage::disk('s3')->assertExists($key);
    }
});

it('keeps a tile preview referenced by absolute CDN url', function () {
    // preview_video_url holds a full CloudFront URL, not a disk key — the sweep
    // has to normalise it or it would delete every hover loop on the site.
    Storage::disk('s3')->put('portfolio/previews/tanqueray.mp4', 'bytes');

    Work::factory()->create([
        'preview_video_url' => 'https://cdn.example.com/portfolio/previews/tanqueray.mp4',
    ]);

    prune();

    Storage::disk('s3')->assertExists('portfolio/previews/tanqueray.mp4');
});

it('keeps a cover referenced by a bare disk key on an industry', function () {
    Storage::disk('s3')->put('verticals/custom-cover.jpg', 'bytes');
    Industry::first()->update(['image_url' => 'verticals/custom-cover.jpg']);

    prune();

    Storage::disk('s3')->assertExists('verticals/custom-cover.jpg');
});

it('keeps an image referenced only by a site setting', function () {
    Storage::disk('s3')->put('uploads/admin-chosen-header.jpg', 'bytes');
    SiteSetting::updateOrCreate(
        ['key' => 'page_image_about'],
        ['value_json' => ['v' => 'uploads/admin-chosen-header.jpg']],
    );

    prune();

    Storage::disk('s3')->assertExists('uploads/admin-chosen-header.jpg');
});

it('deletes nothing on a dry run', function () {
    Storage::disk('s3')->put('portfolio/retired-campaign.jpg', 'bytes');

    prune(['--dry-run' => true]);

    Storage::disk('s3')->assertExists('portfolio/retired-campaign.jpg');
});

it('keeps a client logo, which is a plain key on the model not a media row', function () {
    // The first version of this command missed Client::logo_path. A dry run
    // proposed deleting all eighteen logos, which would have emptied the client
    // marquee on every page of the site.
    Storage::disk('s3')->put('27/dlf.png', 'bytes');
    Client::first()->update(['logo_path' => '27/dlf.png']);

    prune();

    Storage::disk('s3')->assertExists('27/dlf.png');
});

it('keeps an og image referenced by a seo page', function () {
    Storage::disk('s3')->put('social/og-custom.jpg', 'bytes');
    SeoPage::first()->update(['og_image_path' => 'social/og-custom.jpg']);

    prune();

    Storage::disk('s3')->assertExists('social/og-custom.jpg');
});

it('keeps a service hero and every url in its gallery array', function () {
    // gallery_urls is a list, not a single key — a naive string cast would skip
    // the whole column and delete every frame on the service pages.
    Storage::disk('s3')->put('svc/hero.jpg', 'bytes');
    Storage::disk('s3')->put('svc/frame-one.jpg', 'bytes');
    Storage::disk('s3')->put('svc/frame-two.jpg', 'bytes');

    Service::first()->update([
        'hero_url' => 'svc/hero.jpg',
        'gallery_urls' => ['svc/frame-one.jpg', 'svc/frame-two.jpg'],
    ]);

    prune();

    Storage::disk('s3')->assertExists('svc/hero.jpg');
    Storage::disk('s3')->assertExists('svc/frame-one.jpg');
    Storage::disk('s3')->assertExists('svc/frame-two.jpg');
});
