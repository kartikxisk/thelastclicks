<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustriesSeeder extends Seeder
{
    /**
     * Slugs retired from earlier seed versions. A targeted list rather than
     * "delete everything not in $rows" so industries added through the admin
     * panel survive every deploy.
     *
     * The eight-vertical taxonomy below it was invented before the studio's
     * actual client list was mapped. It split the work by market segment
     * (fashion, automotive, nightlife) when the way this studio really sells is
     * by the kind of shoot it runs — a bottle film, a live cover, a site
     * walkthrough. The six that replaced it come from the client list itself.
     *
     * IndustryController::RETIRED_SLUGS 301s these URLs to the deck; keep the
     * two lists in step.
     */
    protected array $retiredSlugs = [
        'corporate-events',
        'brands-products',
        'motion-post-production',
        'motion-graphics',
        'corporate-enterprise',
        'brands-agencies',
        'automobile-luxury',
        'lifestyle-beverage',
        'weddings-celebrations',
        'fashion-creators',
        'nightlife-entertainment',
        'spaces-interiors',
    ];

    public function run(): void
    {
        foreach ($this->rows() as $i => $row) {
            Industry::updateOrCreate(['slug' => $row['slug']], [
                'title' => $row['title'],
                'summary' => $row['summary'],
                // Media-disk path to the bundled cover, so a fresh deploy is never
                // blank. These reuse the images already on the media disk under
                // industries/ rather than shipping six new ones — the photographs
                // are generic enough to carry the new grouping, and an uploaded
                // hero in the admin still overrides this.
                'image_url' => $row['image_url'],
                'body' => $row['body'],
                'order' => $i,
            ]);
        }

        // Hydrate then delete through Eloquent (not a Builder delete) so `deleting`
        // fires and HasMediaItems' cascade cleans up media_items + medialibrary
        // files — a Builder ->delete() bypasses model events and leaks them.
        Industry::whereIn('slug', $this->retiredSlugs)->get()->each->delete();
    }

    /**
     * Each vertical gets its own copy. A shared template across six pages reads
     * as duplicate content to search engines and tells a prospective client
     * nothing about whether we understand their shoot — so the body describes the
     * work, the constraint that actually makes that vertical hard, and what ships.
     *
     * @return list<array{slug: string, title: string, summary: string, image_url: string, body: string}>
     */
    protected function rows(): array
    {
        return [
            [
                'slug' => 'alcobev',
                'title' => 'Alcobev',
                'summary' => 'Bottle films, brand launches and bar activations for spirits — shot to sell the liquid without ever showing anyone drinking it.',
                'image_url' => 'industries/lifestyle-beverage.jpg',
                'body' => <<<'HTML'
                <p>Alcohol is the most heavily restricted category we shoot in India, and the restriction is the brief. Surrogate advertising rules mean the film often cannot show the product being consumed, cannot show anyone under twenty-five, and in a good deal of media cannot show the bottle at all. What is left is craft, provenance and atmosphere — which is precisely why these films live or die on camerawork rather than on a script.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Bottle and liquid films — pours, ice, condensation, macro texture</li>
                    <li>Distillery and provenance stories</li>
                    <li>Bar takeovers, launches and brand nights</li>
                    <li>Bartender and master-blender features</li>
                    <li>Cocktail and serve films for trade and social</li>
                </ul>
                <h3>The hard part</h3>
                <p>Liquid does not take direction. A pour looks right for about a second and a half, so we shoot it at high frame rate and light it to hold highlight detail in the glass without blowing out the label. Glass is a mirror, which means the lighting rig, the crew and the room all end up in shot unless the set is built to keep them out. We black out, flag and polarise as a matter of routine, and we shoot far more takes of a pour than of anything else on the day.</p>
                <h3>What you get</h3>
                <ul>
                    <li>A hero film cut to the compliance constraints your media plan actually faces</li>
                    <li>Vertical cuts for reels and stories, framed at the shoot rather than cropped later</li>
                    <li>Macro and texture plates you can re-cut into future campaigns</li>
                    <li>Stills pulled from the same setups, colour-matched to the film</li>
                </ul>
                <p>We have shot for single malts, tequilas, gins, rums and blended whiskies, in distilleries, hotel bars and built sets. Bring the compliance rules to the pre-production call and the film gets built around them instead of being re-edited past them.</p>
                HTML,
            ],
            [
                'slug' => 'cover-artist',
                'title' => 'Cover Artist',
                'summary' => 'Live music films for playback singers and touring artists — multi-camera, shot to the performance, cut to the track.',
                'image_url' => 'industries/nightlife-entertainment.jpg',
                'body' => <<<'HTML'
                <p>A live music film has one job: make somebody who was not in the room feel the performance. That is a coverage problem before it is an edit problem. The song happens once, the artist does not repeat a phrase for the camera, and the moment the crowd reacts is the moment you most need a camera already pointed at them.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Concerts, festival sets and club nights</li>
                    <li>Studio sessions and unplugged performances</li>
                    <li>Cover releases and single launches</li>
                    <li>Artist profiles, rehearsal and backstage films</li>
                    <li>Vertical performance cuts built for reels</li>
                </ul>
                <h3>The hard part</h3>
                <p>Stage lighting is designed for an audience, not a sensor — hard colour, fast changes, and a performer who moves between a hot key and near-darkness inside a bar. We shoot on cameras that hold their colour under saturated LED, expose for the face rather than the stage, and lock white balance so a lighting cue does not shift skin tone mid-phrase. Audio comes off the desk with an independent room mic, because a board feed alone sounds sterile and a room mic alone is unusable.</p>
                <h3>What you get</h3>
                <ul>
                    <li>A performance film cut to the released track, in sync to the frame</li>
                    <li>Multi-camera coverage including a locked wide you can always cut back to</li>
                    <li>Vertical and square cuts framed at the shoot, not cropped afterwards</li>
                    <li>Crowd and atmosphere plates for the artist's own channels</li>
                </ul>
                <p>We have covered playback singers, indie acts and touring bands across auditoriums, hotel ballrooms and clubs. Send the setlist and the runtime and we will tell you how many cameras the room actually needs.</p>
                HTML,
            ],
            [
                'slug' => 'corporate-shoots',
                'title' => 'Corporate Shoots',
                'summary' => 'Summits, leadership films, product launches and internal comms — multi-camera coverage run to a fixed agenda, cut to survive brand and legal review.',
                'image_url' => 'industries/corporate-enterprise.jpg',
                'body' => <<<'HTML'
                <p>Corporate work has one defining constraint: the agenda does not stop for the crew. A keynote happens once, the CEO has eleven minutes between sessions, and nobody is doing a second take. Everything we bring to a corporate shoot is built around getting it right the first time.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Summits, conferences and AGMs — multi-camera, full-session</li>
                    <li>Leadership and founder interviews</li>
                    <li>Product and capability launches</li>
                    <li>Town halls, all-hands and internal announcements</li>
                    <li>Training, onboarding and process films</li>
                    <li>Culture and recruitment films, and team portraits at scale</li>
                </ul>
                <h3>The hard part</h3>
                <p>We work off your run-of-show, not a shot list we invented. Cameras are positioned to cover the stage, the audience reaction and a clean cutaway, so the edit is never short of an option. Audio comes off the desk feed with an independent backup — the single most common way corporate footage is lost. Crews dress and behave to match the room; on a client-facing event nobody should notice us.</p>
                <h3>What you get</h3>
                <ul>
                    <li>A short highlight film within days, while the event still matters internally</li>
                    <li>Full-session recordings, chaptered for the intranet</li>
                    <li>Subtitled and vertical cuts for social and internal channels</li>
                    <li>A stills library cleared for reuse across decks and comms</li>
                </ul>
                <p>Regulated and listed businesses have review chains, so we build one approval round into the schedule rather than treating it as a surprise.</p>
                HTML,
            ],
            [
                'slug' => 'real-estate',
                'title' => 'Real Estate',
                'summary' => 'Office fit-outs, campuses and residential launches — walkthroughs and site films that read as space rather than as a slideshow.',
                'image_url' => 'industries/spaces-interiors.jpg',
                'body' => <<<'HTML'
                <p>A building is the hardest subject to film honestly. Stills flatter a room; motion exposes it. A walkthrough that drifts and wanders makes even a good space feel small, and a wide-angle lens that makes a room look enormous on screen guarantees a disappointed visit. The job is to show the space as it actually is and still make somebody want to stand in it.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Office fit-outs, headquarters and campus films</li>
                    <li>Residential and commercial launch films</li>
                    <li>Walkthroughs, flythroughs and site progress</li>
                    <li>Amenity, lobby and common-area coverage</li>
                    <li>Interior stills for brochures and listings</li>
                </ul>
                <h3>The hard part</h3>
                <p>Interiors are a dynamic-range problem: the window is several stops brighter than the wall beside it, so either the view blows out or the room goes muddy. We shoot at the hour the building's own glazing works with us, supplement rather than replace the ambient light, and keep verticals genuinely vertical — a leaning doorframe is the tell that separates a property film from an estate-agent clip. Movement is on gimbal or slider with a deliberate start and stop, never a continuous float.</p>
                <h3>What you get</h3>
                <ul>
                    <li>A launch or capability film paced for a sales meeting, not for a scroll</li>
                    <li>Room-by-room segments that can be cut apart for listings</li>
                    <li>Vertical walkthroughs for broker and social distribution</li>
                    <li>A matched stills set for brochures, hoardings and portals</li>
                </ul>
                <p>We have filmed corporate fit-outs, delivery centres and residential launches, and we shoot around occupied buildings — which means working to a security brief and to whoever is still at their desk.</p>
                HTML,
            ],
            [
                'slug' => 'podcast',
                'title' => 'Podcast',
                'summary' => 'Multi-camera podcast and long-form conversation, shot to be cut into an episode and a month of clips from the same session.',
                'image_url' => 'industries/brands-agencies.jpg',
                'body' => <<<'HTML'
                <p>A video podcast is not a filmed radio show. It is a two-hour conversation that has to hold a viewer's attention with almost nothing happening visually, and then survive being cut into thirty vertical clips. Both of those outcomes have to be designed into the shoot, because neither can be rescued in the edit.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Interview and panel podcasts, two to four speakers</li>
                    <li>Founder, leadership and expert long-form</li>
                    <li>Branded series and sponsored episodes</li>
                    <li>Clip-first vertical formats</li>
                    <li>Recurring series on a fixed set</li>
                </ul>
                <h3>The hard part</h3>
                <p>Audio is the whole product — a podcast with poor sound is not watched, however it looks. Each speaker gets their own lavalier and their own track, recorded to a separate device, so one failed channel never costs the episode. Cameras cover a clean single on each speaker plus a two-shot, all locked, so the edit can cut on dialogue rather than on movement. We frame every setup knowing it will be cropped to vertical later, which means keeping heads out of the top third and never letting a speaker drift to the frame edge.</p>
                <h3>What you get</h3>
                <ul>
                    <li>A full episode, colour and audio finished, chaptered</li>
                    <li>Isolated audio stems for a separate podcast-platform release</li>
                    <li>A batch of vertical clips with burned-in subtitles</li>
                    <li>A repeatable set and lighting plot, so episode nine looks like episode one</li>
                </ul>
                <p>For a recurring series the real value is consistency: we document the setup so any subsequent session matches, rather than being rebuilt from memory each time.</p>
                HTML,
            ],
            [
                'slug' => 'wedding-pre-wedding',
                'title' => 'Wedding & Pre-Wedding',
                'summary' => 'Trailers, ceremony films and pre-wedding shoots across India — covered like a live event, cut like a film.',
                'image_url' => 'industries/weddings-celebrations.jpg',
                'body' => <<<'HTML'
                <p>An Indian wedding is a multi-day live event where every important moment is unrepeatable and half of them happen simultaneously in different rooms. There is no second take of a father seeing his daughter, and the ceremony will not pause because a camera was in the wrong place. Coverage planning is the entire craft.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Wedding films and highlight trailers</li>
                    <li>Haldi, mehandi, sangeet and roka</li>
                    <li>Engagements and receptions</li>
                    <li>Pre-wedding and couple shoots, on location</li>
                    <li>Baby showers and family celebrations</li>
                </ul>
                <h3>The hard part</h3>
                <p>Light changes completely between functions — a daylight haldi, a lantern-lit mehandi, a stage-lit sangeet and a dawn ceremony are four different shoots in one weekend, and the film has to feel like one piece. We run a second unit for every function that splits across rooms, put a discreet recorder on the officiant and on the couple so the vows survive the crowd, and shoot the ritual detail during the long stretches rather than only chasing faces. Families are guests at their own wedding; the crew works around them, not the reverse.</p>
                <h3>What you get</h3>
                <ul>
                    <li>A trailer cut fast, while the family is still together</li>
                    <li>A full film with the rituals intact, not just the highlights</li>
                    <li>Vertical cuts for the couple's own sharing</li>
                    <li>A complete stills set alongside the film, from the same crew</li>
                </ul>
                <p>We travel for weddings and we scout venues ahead of the date wherever the schedule allows — most of what goes wrong on a wedding shoot is a room nobody had seen before the morning of.</p>
                HTML,
            ],
        ];
    }
}
