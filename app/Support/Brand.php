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
}
