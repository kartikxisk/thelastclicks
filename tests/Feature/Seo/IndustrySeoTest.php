<?php

use App\Models\Industry;
use App\Models\SeoPage;
use App\Support\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * Search-facing titles for the industry pages.
 *
 * The six detail pages shipped live and in the sitemap with no SeoPage row, so
 * each fell back to the Blade default and rendered a title of brand plus the
 * vertical's internal name — "Alcobev | The Last Clicks (TLC)". No keyword, no
 * location, on six indexable pages.
 *
 * The names are also the studio's filing vocabulary rather than anything a
 * client types: searching "alcobev brand film" returns regulatory journalism
 * about surrogate advertising, not studios. So the titles target the client's
 * language while the page keeps the studio's.
 */
it('gives every industry page its own managed title and description', function () {
    Industry::all()->each(function (Industry $industry): void {
        $row = SeoPage::where('page_url', '/industries/'.$industry->slug)
            ->where('is_active', true)
            ->first();

        expect($row)->not->toBeNull("no SeoPage row for {$industry->slug}")
            ->and($row->title)->not->toBeEmpty()
            ->and($row->meta_description)->not->toBeEmpty();
    });
});

it('does not fall back to the bare vertical name as a title', function () {
    // The failure this replaces: "Alcobev | The Last Clicks (TLC)".
    Industry::all()->each(function (Industry $industry): void {
        $title = SeoPage::where('page_url', '/industries/'.$industry->slug)->value('title');

        expect($title)->not->toBe(Brand::title($industry->title));
    });
});

it('names a location in every industry title', function () {
    // Every one of these is a local commercial query; a title with no place in
    // it competes nationally for work the studio sells within Delhi NCR.
    Industry::all()->each(function (Industry $industry): void {
        $title = SeoPage::where('page_url', '/industries/'.$industry->slug)->value('title');

        expect($title)->toMatch('/Noida|Delhi|India/');
    });
});

it('renders the managed title on the page rather than the default', function () {
    $industry = Industry::orderBy('order')->firstOrFail();
    $row = SeoPage::where('page_url', '/industries/'.$industry->slug)->firstOrFail();

    $this->get('/industries/'.$industry->slug)
        ->assertOk()
        ->assertSee('<title>'.e($row->title).'</title>', false);
});

it('describes the industries deck by the verticals it actually lists', function () {
    // The description named the eight retired verticals — "Fashion, hospitality,
    // beauty, weddings, automotive, corporate and nightlife" — none of which the
    // page has shown since the taxonomy was replaced.
    $description = SeoPage::where('page_url', '/industries')->value('meta_description');

    expect($description)->toBeString()
        ->and(str_contains($description, 'nightlife'))->toBeFalse()
        ->and(str_contains($description, 'hospitality'))->toBeFalse();

    // At least half the live verticals should be recognisable in it, so the
    // description keeps agreeing with the deck when the taxonomy next moves.
    $named = Industry::all()->filter(
        fn (Industry $i) => str_contains(mb_strtolower($description), mb_strtolower(explode(' ', $i->title)[0]))
    );

    expect($named->count())->toBeGreaterThanOrEqual(3);
});
