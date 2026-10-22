<?php

namespace App\Support;

/**
 * The studio's name, and the other ways people write it.
 *
 * Centralised because the schema blocks carry a standing warning that the name
 * has to be byte-identical across Organization, LocalBusiness, WebSite and every
 * publisher node — a mismatched name splits one brand into two entities as far
 * as Google is concerned, and weakens both. It was hardcoded in six templates.
 *
 * ALTERNATE_NAMES is the supported way to tell a search engine that a brand is
 * known by more than one string. Repeating the variants through the visible copy
 * is keyword stuffing, which Google's spam policies name explicitly and demote
 * for, so the variants live here and in schema, not in running prose.
 */
final class Brand
{
    /**
     * The canonical spelling: "TheLastClicks (TLC)", closed up.
     *
     * The owner's brand name (2026-10-22). A spaced "The Last Clicks (TLC)" was
     * briefly canonical and is now only an alternate. The name has to be
     * byte-identical wherever it is an entity — schema, og:site_name, titles,
     * and directory and Business Profile listings — or Google splits one brand
     * into several. Running prose says plain "TheLastClicks"; this constant is for
     * labels and entity names.
     */
    public const NAME = 'TheLastClicks (TLC)';

    /**
     * The other spellings people search and type. "The Last Clicks" is how anyone
     * hearing the name aloud writes it, and TLC is what clients shorten it to.
     *
     * @var list<string>
     */
    public const ALTERNATE_NAMES = ['TheLastClicks', 'The Last Clicks', 'TLC'];

    /**
     * How the brand signs off a <title>.
     *
     * Equal to NAME so the title and the schema name carry the same string; it also gives a search for "TLC" something literal
     * to match in the strongest on-page field, which alternateName alone does not.
     */
    public const TITLE_SUFFIX = self::NAME;

    /**
     * Build a page title: a keyword-led phrase, then the brand.
     *
     * Every page uses this so the pattern cannot drift back into the three
     * different shapes it had before — "About TheLastClicks — …", "Portfolio — …
     * | TheLastClicks" and "Contact TheLastClicks — …" were all in use at once,
     * which spends the most valuable field on the site describing the brand
     * instead of what the page is about.
     */
    public static function title(string $lead): string
    {
        return $lead.' | '.self::TITLE_SUFFIX;
    }
}
