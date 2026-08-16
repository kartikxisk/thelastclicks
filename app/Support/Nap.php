<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Name, Address, Phone — resolved once, from Site Settings.
 *
 * Local ranking punishes a business that states its address two different ways
 * more than one that states it nowhere, so this exists to make a second way
 * impossible. Before it, the homepage built a PostalAddress from admin settings
 * that nothing ever populated (so it emitted none) while the contact page
 * hardcoded the same address into its own LocalBusiness node. Two sources, and
 * the authoritative #organization node was the empty one.
 *
 * Everything here returns null when the underlying settings are blank. That is
 * deliberate and load-bearing: a LocalBusiness node with a half-filled address
 * is invalid per Google's own docs, and an empty footer line is a smaller
 * problem than a wrong one. Callers must handle null rather than substituting a
 * guess — a guessed locality becomes an inconsistent citation the first time it
 * meets a real directory listing.
 */
class Nap
{
    /**
     * PostalAddress node, or null when the city is unset.
     *
     * Locality is the required-field test: an address without one cannot be
     * resolved to a place, so a row carrying only a street is treated as absent.
     *
     * @return array<string, string>|null
     */
    public static function address(): ?array
    {
        $locality = self::str('address_locality');

        if ($locality === null) {
            return null;
        }

        return array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => self::str('address_street'),
            'addressLocality' => $locality,
            'addressRegion' => self::str('address_region'),
            'postalCode' => self::str('address_postal_code'),
            'addressCountry' => self::str('address_country') ?? 'IN',
        ], fn ($v) => $v !== null);
    }

    /**
     * The address as display lines, for rendering to a human.
     *
     * @return list<string>
     */
    public static function addressLines(): array
    {
        if (self::address() === null) {
            return [];
        }

        $region = implode(' ', array_filter([self::str('address_region'), self::str('address_postal_code')]));

        return array_values(array_filter([
            self::str('address_street'),
            self::str('address_locality'),
            $region !== '' ? $region : null,
        ]));
    }

    /**
     * GeoCoordinates node, or null unless both values are present.
     *
     * Cast to float so the JSON carries numbers: a quoted "28.58" is a string to
     * a parser, and the coordinate is the one field where that matters.
     *
     * @return array<string, mixed>|null
     */
    public static function geo(): ?array
    {
        $lat = self::str('geo_latitude');
        $lng = self::str('geo_longitude');

        if ($lat === null || $lng === null) {
            return null;
        }

        return ['@type' => 'GeoCoordinates', 'latitude' => (float) $lat, 'longitude' => (float) $lng];
    }

    /**
     * openingHoursSpecification, or null when days or times are unset.
     *
     * The structured form rather than the "Mo-Sa 10:00-19:00" string it replaces:
     * one is parseable per-day, the other has to be re-parsed by whoever reads it.
     *
     * @return list<array<string, mixed>>|null
     */
    public static function hours(): ?array
    {
        $days = self::days();
        $opens = self::str('hours_opens');
        $closes = self::str('hours_closes');

        if ($days === [] || $opens === null || $closes === null) {
            return null;
        }

        return [[
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $days,
            'opens' => self::time($opens),
            'closes' => self::time($closes),
        ]];
    }

    /**
     * Hours as one human-readable line, e.g. "Mon–Sat, 10:00–19:00".
     *
     * Collapses a contiguous run of days to a range and lists them otherwise, so
     * a studio closed on Wednesdays reads correctly instead of claiming Mon–Sat.
     */
    public static function hoursLabel(): ?string
    {
        $days = self::days();
        $opens = self::str('hours_opens');
        $closes = self::str('hours_closes');

        if ($days === [] || $opens === null || $closes === null) {
            return null;
        }

        $short = array_map(fn (string $d): string => substr($d, 0, 3), $days);
        $indexes = array_map(fn (string $d): int => (int) array_search($d, self::weekdays(), true), $days);
        $contiguous = count($days) > 2 && $indexes === range($indexes[0], $indexes[0] + count($indexes) - 1);

        $label = $contiguous
            ? $short[0].'–'.$short[count($short) - 1]
            : implode(', ', $short);

        return $label.', '.self::time($opens).'–'.self::time($closes);
    }

    /**
     * areaServed as named City nodes.
     *
     * Named cities rather than a Country node: "India" matches nothing a local
     * searcher types, and areaServed is read at the granularity of the query.
     *
     * @return list<array<string, string>>
     */
    public static function areaServed(): array
    {
        $areas = SiteSetting::get('service_areas', []);
        $areas = is_array($areas) ? $areas : [];

        return array_values(array_map(
            fn (string $city): array => ['@type' => 'City', 'name' => $city],
            array_filter(array_map('trim', array_map('strval', $areas)), fn (string $c): bool => $c !== '')
        ));
    }

    public static function mapUrl(): ?string
    {
        return self::str('map_url');
    }

    /**
     * Open days, ordered by weekday rather than by however they were saved.
     *
     * @return list<string>
     */
    private static function days(): array
    {
        $days = SiteSetting::get('hours_days', []);
        $days = is_array($days) ? array_map('strval', $days) : [];

        return array_values(array_intersect(self::weekdays(), $days));
    }

    /** @return list<string> */
    private static function weekdays(): array
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }

    /**
     * Normalise a stored time to HH:MM.
     *
     * Filament's TimePicker persists "10:00:00"; schema.org and a footer line both
     * want "10:00", and the seconds are noise in either.
     */
    private static function time(string $value): string
    {
        return substr($value, 0, 5);
    }

    /** Read a setting as a trimmed non-empty string, or null. */
    private static function str(string $key): ?string
    {
        $value = SiteSetting::get($key);

        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
