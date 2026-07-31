<?php

use App\Jobs\RevalidateFrontend;
use App\Models\Post;
use App\Models\SeoPage;
use App\Models\SiteSetting;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    config([
        'services.frontend.revalidate_url' => 'http://127.0.0.1:3000/api/revalidate',
        'services.frontend.revalidate_secret' => 'test-secret',
    ]);

    Work::create(['title' => 'Tagged Work', 'slug' => 'tagged-work', 'is_published' => true]);
});

it('dispatches a revalidation job when a work is saved', function () {
    Bus::fake();

    Work::published()->firstOrFail()->update(['title' => 'Renamed']);

    Bus::assertDispatched(RevalidateFrontend::class);
});

it('dispatches a revalidation job when a work is deleted', function () {
    Bus::fake();

    Work::published()->firstOrFail()->delete();

    Bus::assertDispatched(RevalidateFrontend::class);
});

it('dispatches when a site setting changes', function () {
    Bus::fake();

    SiteSetting::set('contact_email', 'new@example.com');

    Bus::assertDispatched(
        RevalidateFrontend::class,
        fn (RevalidateFrontend $job) => in_array('settings', $job->tags, true)
    );
});

it('includes the collection tag and the slug tag', function () {
    $work = Work::published()->firstOrFail();

    expect($work->frontendCacheTags())
        ->toContain('works')
        ->toContain('works:'.$work->slug)
        ->toContain('pages:home');
});

it('tags a post save with the post slug', function () {
    $post = Post::published()->firstOrFail();

    expect($post->frontendCacheTags())
        ->toContain('posts')
        ->toContain('posts:'.$post->slug);
});

it('drops every tag when an seo row changes, since metadata touches all routes', function () {
    $tags = (new SeoPage)->frontendCacheTags();

    expect($tags)->toContain('pages:home')->toContain('works')->toContain('posts');
});

it('posts the tags and secret to the configured url', function () {
    Http::fake();

    (new RevalidateFrontend(['works', 'pages:home']))->handle();

    Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:3000/api/revalidate'
        && $request['tags'] === ['works', 'pages:home']
        && $request['secret'] === 'test-secret');
});

it('deduplicates tags before sending', function () {
    Http::fake();

    (new RevalidateFrontend(['works', 'works', 'pages:home']))->handle();

    Http::assertSent(fn ($request) => $request['tags'] === ['works', 'pages:home']);
});

it('does nothing when no revalidate url is configured', function () {
    config(['services.frontend.revalidate_url' => null]);
    Http::fake();

    (new RevalidateFrontend(['works']))->handle();

    Http::assertNothingSent();
});

it('does nothing when the tag list is empty', function () {
    Http::fake();

    (new RevalidateFrontend([]))->handle();

    Http::assertNothingSent();
});

it('swallows a connection failure so a save never breaks', function () {
    Http::fake(fn () => throw new ConnectionException('down'));

    // An editor saving in Filament must never see an error because the
    // frontend happens to be restarting.
    expect(fn () => (new RevalidateFrontend(['works']))->handle())->not->toThrow(Throwable::class);
});
