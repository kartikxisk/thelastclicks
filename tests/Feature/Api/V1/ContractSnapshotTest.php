<?php

use App\Models\HeroSlide;
use App\Models\Post;
use App\Models\Service;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The recorded contract. Committed, diffed on every run, and the file Plan 2's
 * web/src/lib/types.ts is written against.
 *
 * Regenerate deliberately with: UPDATE_CONTRACT=1 ./bin/php vendor/bin/pest
 * --filter=contract — and review the diff, because every change here is a
 * change the frontend has to make in the same pull request.
 */
const CONTRACT_PATH = __DIR__.'/contract.json';

beforeEach(function () {
    $this->seed();

    HeroSlide::create(['label' => 'Reel', 'order' => 0, 'is_active' => true]);

    foreach (range(1, 3) as $i) {
        $work = Work::create([
            'title' => "Work {$i}",
            'slug' => "work-{$i}",
            'summary' => 'Summary.',
            'client' => 'Acme',
            'category' => 'brand-film',
            'crafts' => ['direction'],
            'credits' => [['role' => 'Director', 'name' => 'Ada']],
            'location' => 'London',
            'agency' => 'Agency',
            'year' => '2026',
            'order' => $i,
            'is_published' => true,
            'is_featured' => $i === 1,
        ]);
        $work->mediaItems()->create([
            'type' => 'youtube',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'caption' => 'Clip',
            'order' => 0,
        ]);
    }
});

/**
 * Replace scalars with their type name so the record captures shape rather
 * than seeded content: a changed key or a changed type fails, a changed
 * headline does not.
 */
function shapeOf(mixed $value): mixed
{
    if (! is_array($value)) {
        return get_debug_type($value);
    }

    if ($value === []) {
        return 'array<empty>';
    }

    // Lists collapse to one representative element, or the record would churn
    // whenever a fixture adds a row.
    if (array_is_list($value)) {
        return [shapeOf($value[0])];
    }

    return collect($value)->map(fn ($v) => shapeOf($v))->all();
}

it('matches the recorded contract for every endpoint', function () {
    $paths = [
        '/api/v1/health',
        '/api/v1/settings',
        '/api/v1/pages/home',
        '/api/v1/pages/about',
        '/api/v1/pages/contact',
        '/api/v1/pages/privacy-policy',
        '/api/v1/pages/thank-you',
        '/api/v1/works',
        '/api/v1/services',
        '/api/v1/services/'.Service::firstOrFail()->slug,
        '/api/v1/industries',
        '/api/v1/posts',
        '/api/v1/posts/'.Post::published()->firstOrFail()->slug,
    ];

    $actual = [];
    foreach ($paths as $path) {
        // Slugs differ per seed run; record under a stable key.
        $key = preg_replace('~/(services|posts)/[^/]+$~', '/$1/{slug}', $path);
        $actual[$key] = shapeOf($this->getJson($path)->assertOk()->json());
    }

    if (! file_exists(CONTRACT_PATH) || getenv('UPDATE_CONTRACT')) {
        file_put_contents(
            CONTRACT_PATH,
            json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
        );
    }

    $recorded = json_decode((string) file_get_contents(CONTRACT_PATH), true);

    expect($actual)->toEqual(
        $recorded,
        'API contract changed. If deliberate, regenerate with UPDATE_CONTRACT=1 '
        .'and update web/src/lib/types.ts in the same pull request.'
    );
});

it('records every GET endpoint the router exposes', function () {
    $routed = collect(app('router')->getRoutes()->getRoutes())
        ->filter(fn ($r) => str_starts_with($r->uri(), 'api/v1') && in_array('GET', $r->methods(), true))
        ->map(fn ($r) => '/'.preg_replace('~\{[^}]+\}~', '{slug}', $r->uri()))
        ->unique()
        ->sort()
        ->values();

    $recorded = collect(array_keys(json_decode((string) file_get_contents(CONTRACT_PATH), true)))
        ->map(fn (string $p) => preg_replace('~/pages/(privacy-policy|thank-you)$~', '/pages/{slug}', $p))
        ->unique()
        ->sort()
        ->values();

    // A new GET endpoint that nobody added to the contract is exactly the drift
    // this file exists to catch.
    expect($recorded->all())->toBe($routed->all());
});
