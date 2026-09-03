<?php

use App\Models\Post;
use Database\Seeders\PostMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');
    $this->seed();
});

/**
 * Journal covers survive a rebuilt database.
 *
 * PostsSeeder writes the posts, but a cover is a medialibrary row it knows
 * nothing about — so every card on /blog rendered an empty placeholder. Same
 * fixture trick the service and industry uploads use.
 */
it('replays a post cover onto a rebuilt database', function () {
    $post = Post::query()->firstOrFail();
    $post->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('cover');

    $url = $post->fresh()->getFirstMediaUrl('cover');
    $path = base_path('tests/tmp-post-media.json');

    $this->artisan('app:export-post-media', ['--path' => $path])->assertSuccessful();

    Media::query()->where('model_type', $post->getMorphClass())->delete();
    expect($post->fresh()->getFirstMediaUrl('cover'))->toBe('');

    (new PostMediaSeeder($path))->run();

    expect($post->fresh()->getFirstMediaUrl('cover'))->toBe($url);

    unlink($path);
});

it('leaves a cover an editor already replaced alone', function () {
    $post = Post::query()->firstOrFail();
    $post->addMedia(UploadedFile::fake()->image('cover.jpg'))->toMediaCollection('cover');

    $path = base_path('tests/tmp-post-media.json');
    $this->artisan('app:export-post-media', ['--path' => $path])->assertSuccessful();

    $before = $post->fresh()->getFirstMediaUrl('cover');
    (new PostMediaSeeder($path))->run();

    expect($post->fresh()->getFirstMediaUrl('cover'))->toBe($before)
        ->and($post->fresh()->getMedia('cover'))->toHaveCount(1);

    unlink($path);
});
