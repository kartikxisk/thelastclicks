<?php

use App\Models\Industry;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * The AI-answer surface: what lets an assistant extract and cite this site.
 *
 * robots.txt already allows every AI crawler by name, with the reasoning on
 * record. These cover the two gaps that audit found: FAQs rendered as an
 * accordion with no FAQPage node (so the one block of literal Q&A on the site
 * was invisible to answer engines), and no llms.txt overview.
 */
it('marks up service faqs as FAQPage schema', function () {
    $service = Service::firstOrFail();

    expect($service->faqs)->not->toBeEmpty();

    $html = $this->get('/services/'.$service->slug)->assertOk()->getContent();

    expect($html)->toContain('"FAQPage"')
        // The schema must carry the same questions the page shows, not a
        // parallel set that can drift.
        ->and($html)->toContain(json_encode($service->faqs[0]['q'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
});

it('renders no FAQPage node on a service without faqs', function () {
    $service = Service::firstOrFail();
    $service->update(['faqs' => []]);

    expect($this->get('/services/'.$service->slug)->assertOk()->getContent())
        ->not->toContain('"FAQPage"');
});

it('generates an llms.txt describing the studio from live data', function () {
    Artisan::call('llms:generate');

    $path = public_path('llms.txt');
    expect(File::exists($path))->toBeTrue();

    $txt = File::get($path);

    // Name, what it is, and the pages an assistant should read next.
    expect($txt)->toContain('TheLastClicks')
        ->and($txt)->toContain('/services/post-production')
        ->and($txt)->toContain('/industries/')
        ->and($txt)->toContain('Noida');

    File::delete($path);
});

it('lists every live service and industry in llms.txt', function () {
    Artisan::call('llms:generate');
    $txt = File::get(public_path('llms.txt'));

    Service::all()->each(fn ($s) => expect($txt)->toContain('/services/'.$s->slug));
    Industry::all()->each(fn ($i) => expect($txt)->toContain('/industries/'.$i->slug));

    File::delete(public_path('llms.txt'));
});
