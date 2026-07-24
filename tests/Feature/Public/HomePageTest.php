<?php

use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
