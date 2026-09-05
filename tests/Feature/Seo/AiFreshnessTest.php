<?php

use App\Console\Commands\GeneratePricingMd;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * Freshness a reader (and an answer engine) can see, and the machine-readable
 * pricing file the cost guide made honest.
 */
it('shows a visible updated date once a post has actually been revised', function () {
    $post = Post::query()->firstOrFail();
    $post->timestamps = false;
    $post->forceFill([
        'published_at' => now()->subMonths(6),
        'updated_at' => now()->subDay(),
    ])->save();

    $html = $this->get('/blog/'.$post->slug)->assertOk()->getContent();

    expect($html)->toContain('Updated '.now()->subDay()->format('d M Y'));
});

it('shows no updated date when a post has never been revised', function () {
    // "Updated" on the day it was published is noise pretending to be freshness.
    $post = Post::query()->firstOrFail();
    $post->timestamps = false;
    $post->forceFill([
        'published_at' => now()->subMonths(2),
        'updated_at' => now()->subMonths(2),
    ])->save();

    expect($this->get('/blog/'.$post->slug)->assertOk()->getContent())
        ->not->toContain('Updated ');
});

it('publishes no pricing figures anywhere machine-readable', function () {
    // The studio quotes every project individually and does not show pricing
    // publicly — a decision, not an omission. The quote wizard asks a CLIENT
    // for their budget; content that restated those bands as what work costs
    // crossed that line and was pulled the same day it shipped.
    Artisan::call('llms:generate');

    $txt = File::get(public_path('llms.txt'));

    expect(str_contains($txt, 'pricing.md'))->toBeFalse()
        ->and(str_contains($txt, '₹'))->toBeFalse()
        // What replaces it converts: the enquiry path. It used to carry a
        // "4 working hours" reply window as well; the site now promises no
        // times at all — reply or delivery — so the path is the whole pitch.
        ->and($txt)->toContain(url('/contact'))
        ->and($txt)->not->toContain('working hours');

    expect(File::exists(public_path('pricing.md')))->toBeFalse()
        ->and(class_exists(GeneratePricingMd::class))->toBeFalse();

    File::delete(public_path('llms.txt'));
});

it('keeps the cost guide free of rupee figures and pointed at the quote', function () {
    // The post keeps its value — the six drivers, which prove expertise — but
    // states no bands, and every section funnels to a conversation instead.
    $body = (string) Post::where('slug', 'video-editing-cost-india')->firstOrFail()->body;

    expect(str_contains($body, '₹'))->toBeFalse()
        ->and($body)->toContain('/services/post-production')
        ->and($body)->toContain('/contact');
});
