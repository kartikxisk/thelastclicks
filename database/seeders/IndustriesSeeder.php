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
     */
    protected array $retiredSlugs = [
        'corporate-events',
        'brands-products',
        'motion-post-production',
        'motion-graphics',
    ];

    public function run(): void
    {
        foreach ($this->rows() as $i => $row) {
            Industry::updateOrCreate(['slug' => $row['slug']], [
                'title' => $row['title'],
                'summary' => $row['summary'],
                // Hero image is uploaded through the admin — nothing is seeded.
                'image_url' => null,
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
     * Each vertical gets its own copy. A shared template across eight pages reads
     * as duplicate content to search engines and tells a prospective client
     * nothing about whether we understand their shoot — so the body describes the
     * work, the constraint that actually makes that vertical hard, and what ships.
     *
     * @return list<array{slug: string, title: string, summary: string, body: string}>
     */
    protected function rows(): array
    {
        return [
            [
                'slug' => 'corporate-enterprise',
                'title' => 'Corporate & Enterprise',
                'summary' => 'Conferences, leadership films, town halls and internal comms — multi-camera coverage run to a fixed agenda, cut to survive brand and legal review.',
                'body' => <<<'HTML'
                <p>Corporate work has one defining constraint: the agenda does not stop for the crew. A keynote happens once, the CEO has eleven minutes between sessions, and nobody is doing a second take. Everything we bring to an enterprise shoot is built around getting it right the first time.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Conferences, summits and AGMs — multi-camera, full-session</li>
                    <li>Leadership and founder interviews</li>
                    <li>Town halls, all-hands and internal announcements</li>
                    <li>Training, onboarding and process films</li>
                    <li>Culture and recruitment films</li>
                    <li>Executive headshots and team portraits at scale</li>
                </ul>
                <h3>How it runs</h3>
                <p>We work off your run-of-show, not a shot list we invented. Cameras are positioned to cover the stage, the audience reaction and a clean cutaway, so the edit is never short of an option. Audio comes off the desk feed with an independent backup — the single most common way corporate footage is lost. Crews dress and behave to match the room; on a client-facing event nobody should notice us.</p>
                <h3>What you get</h3>
                <ul>
                    <li>A short highlight film within days, while the event still matters internally</li>
                    <li>Full-session recordings, chaptered for the intranet</li>
                    <li>Subtitled and vertical cuts for social and internal channels</li>
                    <li>A stills library cleared for reuse across decks and comms</li>
                </ul>
                <p>Regulated and listed businesses have review chains, so we build one approval round into the schedule rather than treating it as a surprise. If your team needs to prepare before the day, our note on <a href="/blog/preparing-your-team-for-a-corporate-shoot">preparing for a corporate shoot</a> is the briefing we send clients.</p>
                HTML,
            ],
            [
                'slug' => 'brands-agencies',
                'title' => 'Brands & Agencies',
                'summary' => 'Campaign films, product and ecommerce content produced to agency spec — storyboard-accurate, brand-guideline compliant, delivered in every cut you need.',
                'body' => <<<'HTML'
                <p>Agency work is a different job from client-direct work. The thinking is usually done, the storyboard is approved, and what is needed is a production partner who executes the frame that was sold — not one who quietly reinterprets it on the day.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Campaign and brand films</li>
                    <li>Product films and packshots</li>
                    <li>Ecommerce photography — on-white, on-model, in-context</li>
                    <li>Lifestyle content libraries for always-on channels</li>
                    <li>Social-first vertical content and ambassador shoots</li>
                </ul>
                <h3>How it runs</h3>
                <p>We can work from your storyboard or write the treatment ourselves — both are normal. Where a board exists, we shoot it frame for frame and flag any deviation before we move on rather than after the wrap. Product work is colour-managed end to end, because a shade that drifts between the shoot and the site is a returns problem, not an aesthetic one. Shot lists are signed off before the camera is unpacked.</p>
                <h3>What you get</h3>
                <ul>
                    <li>Masters plus every platform cut — 16:9, 1:1, 9:16, with and without subtitles</li>
                    <li>Retouched stills to your spec, with layered files on request</li>
                    <li>Clear usage and licensing terms agreed up front, not after delivery</li>
                    <li>Organised, named deliverables your team can hand straight to trafficking</li>
                </ul>
                <p>Post can be booked on its own if the footage already exists — see <a href="/services/post-production">post-production</a>.</p>
                HTML,
            ],
            [
                'slug' => 'automobile-luxury',
                'title' => 'Automobile & Luxury',
                'summary' => 'Automotive films, launches and luxury product work — controlled light on difficult surfaces, controlled motion, and colour that survives the grade.',
                'body' => <<<'HTML'
                <p>Cars and luxury goods are hard to photograph for the same reason: the surface is the product. Paint, chrome, glass and polished metal do not have a colour of their own — they show you the room. Most of the craft in this vertical is controlling what those surfaces are allowed to reflect.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Static studio and location vehicle photography</li>
                    <li>Rolling and tracking films</li>
                    <li>Interior, detail and material macro</li>
                    <li>Launch events, unveils and dealership content</li>
                    <li>Watches, jewellery and luxury goods</li>
                </ul>
                <h3>How it runs</h3>
                <p>In studio we shape large, soft sources and read the panel rather than the meter, then clean up reflections in the grade instead of fighting them on set. On location we shoot the edges of the day, use polarisers deliberately, and scout for what will land in the bodywork before we care about the background. Rolling shots run off a tracking vehicle or gimbal with a camera car brief agreed in advance; road work is permitted and marshalled properly, never improvised in traffic.</p>
                <h3>What you get</h3>
                <ul>
                    <li>A hero film plus cutdowns for launch, social and dealer channels</li>
                    <li>Colour-accurate stills — body colour matched to the manufacturer reference</li>
                    <li>Detail and interior sets for configurators and brochures</li>
                    <li>Retouching that removes rigs and reflections without plasticising the paint</li>
                </ul>
                <p>Grading is done in-house on calibrated displays, so what is approved is what ships. More on the finish in <a href="/services/post-production">post-production</a>.</p>
                HTML,
            ],
            [
                'slug' => 'lifestyle-beverage',
                'title' => 'Lifestyle & Beverage',
                'summary' => 'Food, drink and lifestyle production — including regulated categories, where the compliance brief matters as much as the light.',
                'body' => <<<'HTML'
                <p>Food and drink is a timing discipline. A pour holds its shape for a fraction of a second, condensation runs within a minute of leaving the fridge, and a garnish wilts under a key light. The shot is usually decided before anything is poured — the shoot itself is executing a plan quickly enough that the product still looks alive.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Pour, splash and serve sequences at high frame rate</li>
                    <li>Cocktail and serve-suggestion films</li>
                    <li>Food photography for menus, delivery platforms and packaging</li>
                    <li>Bar, restaurant and venue atmosphere</li>
                    <li>Brand-world lifestyle libraries</li>
                </ul>
                <h3>How it runs</h3>
                <p>We shoot with a stylist or home economist on set for anything where the product is eaten or drunk, and we build multiples so the hero is always fresh. Glassware is prepped, polished and lit for the liquid, not the label. High-speed capture handles pours and ice; the rest is patience.</p>
                <p>Alcohol is a regulated category in India, and the advertising rules shape the creative before we get to the frame. We plan those shoots with the restrictions written into the treatment — what can be shown, what cannot be depicted, and which cuts are for which channel — so nothing has to be rescued in the edit or pulled after publication. Bring your compliance team into the treatment stage and the shoot gets easier, not harder.</p>
                <h3>What you get</h3>
                <ul>
                    <li>Hero film plus serve, recipe and vertical cuts</li>
                    <li>Stills for packaging, menu, delivery platforms and social</li>
                    <li>Channel-appropriate versions where the category is restricted</li>
                </ul>
                HTML,
            ],
            [
                'slug' => 'weddings-celebrations',
                'title' => 'Weddings & Celebrations',
                'summary' => 'Weddings, preweddings, anniversaries and birthdays — multi-day, multi-venue coverage built around moments that will not happen twice.',
                'body' => <<<'HTML'
                <p>Weddings are the only work we do where a technical failure cannot be fixed by reshooting. That single fact drives how we crew, how we back up, and why we are boring about planning.</p>
                <h3>What we cover</h3>
                <ul>
                    <li>Prewedding and engagement shoots</li>
                    <li>Haldi, mehendi and sangeet</li>
                    <li>Ceremony and rituals, in full</li>
                    <li>Reception and after-parties</li>
                    <li>Anniversaries, milestone birthdays and family celebrations</li>
                </ul>
                <h3>How it runs</h3>
                <p>Multi-day functions run with more than one unit, so the couple getting ready and the guests arriving are both covered without anyone sprinting between rooms. Cameras record to two cards at once and cards are copied to two drives before the crew sleeps. We shoot discreetly — long lenses and available light wherever the room allows — because the photographs people keep are almost never the posed ones.</p>
                <p>The single biggest predictor of good wedding coverage is not the crew size, it is the timeline. Ceremonies run late, the light goes, and the portraits that mattered get compressed into eleven rushed minutes. We build a realistic schedule with you in advance; our <a href="/blog/wedding-photography-timeline-planning">wedding photography timeline template</a> is the working document we start from.</p>
                <h3>What you get</h3>
                <ul>
                    <li>A short teaser while the family is still together</li>
                    <li>A feature film of the wedding</li>
                    <li>Full, unedited ceremony coverage for the family archive</li>
                    <li>An album-ready edited stills set, plus the wider selects</li>
                </ul>
                HTML,
            ],
            [
                'slug' => 'fashion-creators',
                'title' => 'Fashion & Creators',
                'summary' => 'Runway, lookbook and designer portfolio work, plus creator and ambassador content — shot fast, cut faster, styled to the garment.',
                'body' => <<<'HTML'
                <p>Fashion splits into two very different jobs. A runway show is live coverage with no retakes and a same-night deadline. A lookbook is a controlled build where every fold and hem is deliberate. We staff and schedule them differently.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Runway shows — full look-by-look plus backstage</li>
                    <li>Lookbooks and campaign editorial</li>
                    <li>Designer portfolios and craft films</li>
                    <li>Creator, influencer and ambassador content</li>
                    <li>Ecommerce on-model and flat-lay</li>
                </ul>
                <h3>How it runs</h3>
                <p>At a show, positions are locked with the organiser before doors open — end-of-runway for the look-by-look, a roaming unit for backstage and front row. Exposure is set for the runway wash and left alone, because a show is not the moment to chase changing light. Selects go out while the audience is still in the room.</p>
                <p>Lookbooks are the opposite: slow, styled, and shot for movement. Garments are steamed, pinned and dressed between frames, and we shoot enough motion that the fabric reads as fabric rather than a still that happens to move. Creator work sits in between — briefed like a campaign, shot loose enough to stay native to the platform.</p>
                <h3>What you get</h3>
                <ul>
                    <li>Show film and backstage cut, turned around same-night where needed</li>
                    <li>Clean look-by-look stills, ordered and named by exit</li>
                    <li>Vertical-first cuts for the creator's own channels</li>
                    <li>Retouching that keeps texture and true garment colour</li>
                </ul>
                HTML,
            ],
            [
                'slug' => 'nightlife-entertainment',
                'title' => 'Nightlife & Entertainment',
                'summary' => 'Club nights, concerts, festivals and artist content — coverage built for darkness, crowds and stage lighting nobody controls but the LD.',
                'body' => <<<'HTML'
                <p>Live entertainment is the hardest lighting environment we work in, and the one where you have the least control. The lighting designer is running the room, the fixtures are moving, the colour temperature changes every eight bars, and the subject is a crowd that will not hold still. The answer is not more light — adding our own would destroy the atmosphere people came for. The answer is faster glass and a camera that holds up when it is pushed.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Club nights and residencies</li>
                    <li>Concerts, tours and festival stages</li>
                    <li>Aftermovies and season recaps</li>
                    <li>Artist EPKs, press shots and release content</li>
                    <li>Venue and brand-activation coverage</li>
                </ul>
                <h3>How it runs</h3>
                <p>We shoot wide-aperture and expose for the highlights on stage, letting the room fall away rather than lifting it into noise. Stage and pit access is agreed with the promoter and the artist's team in advance — nothing kills coverage faster than being moved during the headline set. Audio is taken from the desk wherever possible and synced to camera, because a phone-grade mix ruins an otherwise good aftermovie. Rigs stay compact and crowd-safe.</p>
                <h3>What you get</h3>
                <ul>
                    <li>An aftermovie cut to the energy of the night, not the run order</li>
                    <li>Vertical cuts for the artist and venue channels</li>
                    <li>Stills sets for press and next-season promotion</li>
                    <li>Fast turnaround, because event content ages in days</li>
                </ul>
                HTML,
            ],
            [
                'slug' => 'spaces-interiors',
                'title' => 'Spaces & Interiors',
                'summary' => 'Hotels, restaurants, retail and residences — architectural discipline, honest natural light, and scheduling built around the hour the space looks best.',
                'body' => <<<'HTML'
                <p>Interiors reward patience more than equipment. A room has perhaps two good hours in a day, and the difference between a space that looks expensive and one that looks like a listing photo is almost entirely light discipline and straight verticals.</p>
                <h3>What we shoot</h3>
                <ul>
                    <li>Hotels, resorts and hospitality suites</li>
                    <li>Restaurants, cafés and bars</li>
                    <li>Retail interiors and flagship stores</li>
                    <li>Residences, villas and show homes</li>
                    <li>Offices, studios and workspaces</li>
                </ul>
                <h3>How it runs</h3>
                <p>Verticals are kept true — converging walls are the fastest way to make a good room look amateur. We shoot for the windows and blend exposures where the view matters, rather than blowing it out and calling it bright. Rooms are dressed, not just tidied: we bring a stylist for hospitality work, and we will move furniture to make a composition sit.</p>
                <p>Scheduling is the real craft. We plan the day around which elevation gets light when, and hold the exterior for blue hour, when the interior glow finally balances the sky. That single window is usually the frame the client ends up leading with.</p>
                <h3>What you get</h3>
                <ul>
                    <li>A full stills set — wides, vignettes and detail</li>
                    <li>A walkthrough film for the site and booking platforms</li>
                    <li>Vertical cuts formatted for listing and travel platforms</li>
                    <li>Twilight exteriors where the building supports them</li>
                </ul>
                HTML,
            ],
        ];
    }
}
