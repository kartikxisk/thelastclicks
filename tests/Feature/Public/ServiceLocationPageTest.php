<?php

use App\Filament\Resources\ServiceLocationResource\Pages\ListServiceLocations;
use App\Http\Middleware\RedirectTrailingSlash;
use App\Models\Service;
use App\Models\ServiceLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    // The seeder loads the real copy file when present; tests build their own rows.
    ServiceLocation::query()->delete();
    $this->service = Service::where('slug', 'videography')->firstOrFail();
    $this->makeLoc = fn (Service $s, string $slug, string $name, array $o = []) => ServiceLocation::create($o + [
        'service_id' => $s->id, 'city_slug' => $slug, 'city_name' => $name,
        'headline' => $s->title.' in '.$name, 'intro' => '<p>Intro.</p>',
        'sections' => [['heading' => 'Section', 'body' => '<p>Body.</p>']],
        'meta_title' => 'Lead '.$name, 'meta_description' => 'Description.',
    ]);
});

function jsonLdNodes(string $html): array
{
    preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);

    return array_map(fn ($j) => json_decode($j, true), $m[1]);
}

it('renders a published location with H1 and a City-scoped Service node', function () {
    ($this->makeLoc)($this->service, 'delhi', 'Delhi');

    $res = $this->get('/services/videography/delhi')->assertOk();
    $res->assertSee('<h1>', false);

    $nodes = collect(jsonLdNodes($res->getContent()));
    $svc = $nodes->first(fn ($n) => ($n['@type'] ?? null) === 'Service' || ($n['@graph'][0]['@type'] ?? null) === 'Service');
    $svc = $svc['@graph'][0] ?? $svc;
    expect($svc['areaServed']['@type'])->toBe('City');
    // Scoped to the JSON-LD blocks: the layout chrome is not this page's schema.
    $ld = json_encode($nodes->all(), JSON_UNESCAPED_UNICODE);
    expect($ld)->not->toContain('priceRange')->not->toContain('₹')->not->toContain('Offer')->not->toContain('FAQPage');
});

it('404s for unpublished locations and unknown cities', function () {
    ($this->makeLoc)($this->service, 'noida', 'Noida', ['is_published' => false]);

    $this->get('/services/videography/noida')->assertNotFound();
    $this->get('/services/videography/atlantis')->assertNotFound();
    $this->get('/services/nope/delhi')->assertNotFound();
});

it('leaves the single-segment service page alone', function () {
    $this->get('/services/videography')->assertOk();
});

it('adds published locations to the sitemap', function () {
    ($this->makeLoc)($this->service, 'delhi', 'Delhi');
    ($this->makeLoc)($this->service, 'noida', 'Noida', ['is_published' => false]);

    $this->artisan('sitemap:generate', ['--force' => true])->assertSuccessful();
    $xml = file_get_contents(public_path('sitemap.xml'));
    @unlink(public_path('sitemap.xml'));

    expect($xml)->toContain('/services/videography/delhi')->not->toContain('/services/videography/noida');
});

it('links locations from the service page but not the footer', function () {
    ($this->makeLoc)($this->service, 'delhi', 'Delhi');

    $this->get('/services/videography')->assertSee('data-service-locations', false)->assertSee('/services/videography/delhi', false);
    // Owner's call: the footer stays free of city links; the service page and
    // the sitemap are how these pages are reached.
    $this->get('/about')->assertDontSee('/services/videography/delhi', false);
});

it('lists city pages for an admin', function () {
    $loc = ($this->makeLoc)($this->service, 'delhi', 'Delhi');
    $this->actingAs(User::where('email', config('app.admin_seed_email'))->first());

    Livewire::test(ListServiceLocations::class)->assertCanSeeTableRecords([$loc]);
});

it('lets Editor manage city pages', function () {
    $loc = ($this->makeLoc)($this->service, 'delhi', 'Delhi');
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    expect($editor->can('update', $loc))->toBeTrue();
});

it('meets the on-page SEO structure', function () {
    ($this->makeLoc)($this->service, 'delhi', 'Delhi');
    ($this->makeLoc)($this->service, 'noida', 'Noida');
    $html = $this->get('/services/videography/delhi')->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1);
    expect($html)->toContain('<h2>Section</h2>')->not->toContain('<h3>Section');
    expect($html)->toContain('<link rel="canonical" href="'.url('/services/videography/delhi').'"');
    expect($html)->toContain('<title>Lead Delhi | ');
    expect($html)->toContain('name="description" content="Description."');
    expect($html)->toContain('property="og:title"')->toContain('name="twitter:');
    expect($html)->not->toMatch('/noindex/i');
    expect($html)->toContain('Videography in other cities')->toContain('/services/videography/noida')->not->toContain('click here')
        ->toMatch('#href="[^"]*/services/videography/noida">Videography in Noida#');

    $crumbs = collect(jsonLdNodes($html))
        ->map(fn ($n) => $n['@graph'] ?? [$n])->flatten(1)
        ->first(fn ($n) => ($n['@type'] ?? null) === 'BreadcrumbList');
    expect(collect($crumbs['itemListElement'])->pluck('position')->all())->toBe([1, 2, 3]);
    expect(collect($crumbs['itemListElement'])->pluck('item')->every(fn ($u) => str_starts_with($u, 'http')))->toBeTrue();

    $service = collect(jsonLdNodes($html))->map(fn ($n) => $n['@graph'] ?? [$n])->flatten(1)
        ->first(fn ($n) => ($n['@type'] ?? null) === 'Service');
    expect($service['name'])->toBe('Videography in Delhi')
        ->and($service['url'])->toBe(url('/services/videography/delhi'))
        ->and($service['provider'])->toBe(['@id' => url('/').'#organization']);
});

it('redirects the trailing-slash variant', function () {
    // Middleware driven directly: the test client strips trailing slashes.
    $r = (new RedirectTrailingSlash)->handle(
        Request::create('http://localhost/services/videography/delhi/', 'GET'),
        fn () => new Response('passed'),
    );

    expect($r->getStatusCode())->toBe(301)
        ->and($r->headers->get('Location'))->toBe('http://localhost/services/videography/delhi');
});

it('renders highlights in an aside and the CTA and links once each', function () {
    $other = Service::where('slug', '!=', 'videography')->firstOrFail();
    ($this->makeLoc)($this->service, 'delhi', 'Delhi', ['highlights' => ['Based in Delhi NCR', 'Crew of four']]);
    ($this->makeLoc)($this->service, 'noida', 'Noida');
    ($this->makeLoc)($other, 'delhi', 'Delhi');

    $html = $this->get('/services/videography/delhi')->assertOk()->getContent();

    expect($html)->toMatch('~<aside[^>]*loc-glance[^>]*>\s*<h2[^>]*>At a glance</h2>\s*<ul[^>]*>.*Based in Delhi NCR.*</ul>~s');
    expect($html)->toContain('data-quote-trigger');
    expect(substr_count($html, 'class="btn btn--red loc-rail__cta"'))->toBe(1);
    expect(substr_count($html, 'href="'.url('/services/videography/noida').'"'))->toBe(1);
    expect(substr_count($html, 'href="'.url('/services/'.$other->slug.'/delhi').'"'))->toBe(1);
});

it('omits the glance card and Q&A when there are none', function () {
    ($this->makeLoc)($this->service, 'delhi', 'Delhi');
    $this->get('/services/videography/delhi')->assertOk()
        ->assertDontSee('At a glance')->assertDontSee('Questions about');
});

it('renders faqs as h3s under one h2 with valid heading order and no FAQPage', function () {
    ($this->makeLoc)($this->service, 'delhi', 'Delhi', ['faqs' => [
        ['q' => 'Do you travel?', 'a' => '<p>Yes.</p>'],
        ['q' => 'Do you shoot weddings?', 'a' => '<p>No.</p>'],
    ]]);
    $html = $this->get('/services/videography/delhi')->assertOk()->getContent();

    expect($html)->toContain('<h2>Questions about videography in Delhi</h2>')
        ->toContain('<h3>Do you travel?</h3>')->toContain('<h3>Do you shoot weddings?</h3>')
        ->not->toContain('FAQPage');
    expect(substr_count($html, '<h1'))->toBe(1);

    // Strip chrome: only this page's main content matters for heading order.
    preg_match('~<main.*?</main>~s', $html, $m);
    preg_match_all('~<h([1-6])[\s>]~', $m[0] ?? $html, $h);
    $prev = 0;
    foreach (array_map('intval', $h[1]) as $level) {
        expect($level - $prev)->toBeLessThanOrEqual(1);
        $prev = $level;
    }
});

it('persists highlights and faqs as arrays', function () {
    $file = database_path('seeders/data/service_locations.php');
    $row = collect(require $file)->first();
    $loc = ServiceLocation::create([
        'service_id' => Service::where('slug', $row['service_slug'])->value('id'),
        'city_slug' => 'seed-test', 'city_name' => 'X', 'headline' => 'X', 'intro' => 'x',
        'sections' => [], 'highlights' => ['a'], 'faqs' => [['q' => 'q', 'a' => 'a']],
    ]);

    expect($loc->fresh()->highlights)->toBe(['a'])->and($loc->fresh()->faqs)->toBe([['q' => 'q', 'a' => 'a']]);
});
