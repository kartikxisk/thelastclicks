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
 * known by more than one string, and it is the whole of the job: repeating the
 * variants through the visible copy is keyword stuffing, which Google's spam
 * policies name explicitly and demote for. One declaration that a search engine
 * reads beats fifty mentions it penalises.
 */
final class Brand
{
    /** The canonical spelling. Everything else defers to this. */
    public const NAME = 'TheLastClicks';

    /**
     * The spellings people actually search and type. The closed-up NAME is the
     * odd one out in normal English, so these are not edge cases — "The Last
     * Clicks" is how anyone hearing the name aloud would write it, and TLC is
     * what clients shorten it to.
     *
     * Two entries, not three: "The Last Clicks (TLC)" was declared on the contact
     * page and is a way of WRITING the name, not a name anyone types into a search
     * box. As an entity string it is noise — both of its halves are already here.
     *
     * @var list<string>
     */
    public const ALTERNATE_NAMES = ['The Last Clicks', 'TLC'];

    /**
     * How the brand signs off a <title>.
     *
     * Spaced and parenthesised rather than the closed-up NAME: a title is read by
     * a person scanning a results page, and it is the one place worth spelling the
     * name the way they would say it. Carrying (TLC) here also means a search for
     * the abbreviation has something literal to match in the strongest on-page
     * field, which alternateName in the schema alone does not give.
     */
    public const TITLE_SUFFIX = 'The Last Clicks (TLC)';

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
