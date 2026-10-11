<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('advertises a count on each chip that matches the cards it filters to', function () {
    // The invariant the "Corporate 03" bug broke: the counts were summed over
    // every category a post carries, while the filter compared one slug — the
    // first — so a chip could promise three posts and reveal one. Asserted on
    // the rendered markup because the mismatch only existed there.
    $html = $this->get('/blog')->assertOk()->getContent();

    preg_match_all('~data-cat="([^"]+)"[^>]*aria-pressed~', (string) $html, $chipMatches);
    preg_match_all('~<span class="count">(\d+)</span>~', (string) $html, $countMatches);
    preg_match_all('~data-cat-slugs="([^"]*)"~', (string) $html, $cardMatches);

    // The counts above are only honest if the filter reads the same attribute.
    expect((string) $html)->toContain('dataset.catSlugs');

    $chips = array_combine($chipMatches[1], $countMatches[1]);
    expect($chips)->not->toBeEmpty();

    $cards = array_map(fn (string $slugs): array => preg_split('~\s+~', $slugs) ?: [], $cardMatches[1]);

    foreach ($chips as $slug => $advertised) {
        $matching = $slug === 'all'
            ? count($cards)
            : count(array_filter($cards, fn (array $slugs): bool => in_array($slug, $slugs, true)));

        expect($matching)->toBe((int) $advertised, "chip [{$slug}] advertises {$advertised} and filters to {$matching}");
    }
});
