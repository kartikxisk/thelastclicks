<?php

use App\Models\SiteSetting;
use App\Support\Nap;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * One address, stated the same way everywhere.
 *
 * The homepage built its Organization address from Site Settings and refused to
 * hardcode a fallback; the contact page hardcoded the same address into its own
 * LocalBusiness node. Nothing populated those settings — they were in neither
 * the seeder nor the settings form — so the canonical #organization node emitted
 * no address at all while a second copy of it sat on /contact. Local ranking
 * punishes two different answers more than one missing one.
 */
it('emits the same address on the homepage and the contact page', function () {
    $home = $this->get('/')->assertOk()->getContent();
    $contact = $this->get('/contact')->assertOk()->getContent();

    $address = Nap::address();
    expect($address)->not->toBeNull();

    foreach (['streetAddress', 'addressLocality', 'addressRegion', 'postalCode'] as $field) {
        expect($home)->toContain($address[$field]);
        expect($contact)->toContain($address[$field]);
    }
});

it('renders the address on every page, not only on contact', function () {
    // Footer NAP. A crawler reading any other page could previously find the
    // studio's name and phone but never its city.
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain(SiteSetting::get('address_locality'));
    expect($html)->toContain(Nap::hoursLabel());
});

it('types the contact page as a photography studio, not a generic business', function () {
    $html = $this->get('/contact')->assertOk()->getContent();

    // Decoded rather than string-matched: json_encode emits no space after the
    // colon, and a test that pins the whitespace breaks on an encoder flag change
    // rather than on the thing it is meant to guard.
    preg_match_all('~<script[^>]*application/ld\+json[^>]*>(.*?)</script>~s', $html, $matches);
    $nodes = array_map(fn (string $json): array => json_decode($json, true), $matches[1]);
    $business = collect($nodes)->firstWhere('@type', 'PhotographStudio');

    // schema.org ships an exact subtype for this business; the more specific type
    // is the one a parser can act on.
    expect($business)->not->toBeNull();
    // Linked to the canonical brand node — without it the two are, to a parser,
    // unrelated businesses that happen to share a name.
    expect($business['@id'])->toBe(url('/').'#localbusiness');
    expect($business['parentOrganization']['@id'])->toBe(url('/').'#organization');
});

it('states opening hours in the structured form', function () {
    $html = $this->get('/contact')->assertOk()->getContent();

    expect($html)->toContain('openingHoursSpecification');
    // The old free-text form is gone: it could not express a midweek closure
    // without being rewritten by hand.
    expect($html)->not->toContain('"openingHours"');
});

it('names the cities it serves rather than the whole country', function () {
    $html = $this->get('/contact')->assertOk()->getContent();

    expect(Nap::areaServed())->not->toBeEmpty();
    foreach (Nap::areaServed() as $area) {
        expect($html)->toContain($area['name']);
    }
});

it('emits no address at all when the settings are blank', function () {
    // Deliberate: a half-filled PostalAddress is invalid per Google's own docs,
    // and an empty footer line is a smaller problem than a wrong address.
    foreach (['address_street', 'address_locality', 'address_region', 'address_postal_code'] as $key) {
        SiteSetting::set($key, '');
    }

    expect(Nap::address())->toBeNull();
    expect(Nap::addressLines())->toBe([]);

    $this->get('/contact')->assertOk();
    $this->get('/')->assertOk();
});

it('orders opening days by weekday, whatever order they were saved in', function () {
    SiteSetting::set('hours_days', ['Saturday', 'Monday', 'Wednesday']);

    expect(Nap::hours()[0]['dayOfWeek'])->toBe(['Monday', 'Wednesday', 'Saturday']);
    // Not contiguous, so it lists rather than inventing a "Mon–Sat" the studio
    // does not actually keep.
    expect(Nap::hoursLabel())->toBe('Mon, Wed, Sat, 10:00–19:00');
});

it('collapses a contiguous run of days into a range', function () {
    expect(Nap::hoursLabel())->toBe('Mon–Sat, 10:00–19:00');
});

it('drops the seconds Filament stores on a time', function () {
    SiteSetting::set('hours_opens', '09:30:00');

    expect(Nap::hours()[0]['opens'])->toBe('09:30');
});

it('emits coordinates as numbers, not strings', function () {
    $geo = Nap::geo();

    expect($geo['latitude'])->toBeFloat()->toBe(28.5808331);
    expect($geo['longitude'])->toBeFloat();
});
