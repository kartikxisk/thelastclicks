<?php

use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('renders the homepage with a hero headline', function () {
    // Asserts the hero is present and populated rather than the exact tagline —
    // the headline is marketing copy that gets rewritten, and pinning the words
    // here just breaks the suite every time someone edits it.
    $html = $this->get('/')->assertOk()->assertSee('TheLastClicks')->getContent();

    expect($html)->toContain('class="hero__title"');

    preg_match('~<h1 class="hero__title"[^>]*>(.*?)</h1>~s', $html, $m);

    expect(trim(strip_tags($m[1] ?? '')))->not->toBe('');
});

it('homepage emits Organization JSON-LD', function () {
    $this->get('/')->assertSee('"@type":"Organization"', false);
});

it('homepage shows seeded testimonials from the database', function () {
    $this->get('/')->assertOk()->assertSee('Priya Mehta');
});

it('homepage hides testimonial section when none published', function () {
    Testimonial::query()->update(['is_published' => false]);
    $this->get('/')->assertOk()->assertDontSee('What our');
});

it('renders the services strips with each service linked and its artwork bled full-width', function () {
    // The services section is a stack of full-bleed cover strips: every
    // published service gets one, linking its detail page, carrying its own
    // hero artwork. Deliberately no description copy — the strip is title
    // and photograph only.
    $html = $this->get('/')->assertOk()->getContent();

    preg_match('#<section[^>]*data-svc-index.*?</section>#s', $html, $section);
    expect($section)->not->toBeEmpty();

    foreach (Service::orderBy('order')->get() as $service) {
        expect($section[0])->toContain('href="'.url('/services/'.$service->slug).'"');
    }

    expect($section[0])->not->toContain('svcx__more');
});

it('paints a services strip with the hero image uploaded against that service', function () {
    // The strip's background is Service::heroUrl(), which prefers admin-uploaded
    // media over the seeded hero_url path. That makes the upload under
    // Content → Services the way an editor changes what the homepage shows —
    // worth pinning, because the strip redesign is what gave that field a
    // second home and nothing else asserts the link.
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    $service = Service::orderBy('order')->firstOrFail();
    $service->addMedia(UploadedFile::fake()->image('strip.jpg'))->toMediaCollection('hero');

    $html = $this->get('/')->assertOk()->getContent();

    preg_match('#<section[^>]*data-svc-index.*?</section>#s', $html, $section);

    expect($section)->not->toBeEmpty()
        ->and($section[0])->toContain('--svcx-bg')
        ->and($section[0])->toContain($service->fresh()->heroUrl());
});
