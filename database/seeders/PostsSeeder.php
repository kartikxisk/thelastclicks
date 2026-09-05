<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::first();

        $categories = collect([
            'Production' => Category::firstOrCreate(['name' => 'Production']),
            'Weddings' => Category::firstOrCreate(['name' => 'Weddings']),
            'Corporate' => Category::firstOrCreate(['name' => 'Corporate']),
            'Post-production' => Category::firstOrCreate(['name' => 'Post-production']),
        ]);

        $tags = collect([
            'pre-production' => Tag::firstOrCreate(['name' => 'pre-production']),
            'planning' => Tag::firstOrCreate(['name' => 'planning']),
            'editing' => Tag::firstOrCreate(['name' => 'editing']),
            'brand-films' => Tag::firstOrCreate(['name' => 'brand films']),
            'events' => Tag::firstOrCreate(['name' => 'events']),
        ]);

        foreach ($this->posts() as $i => $data) {
            $post = Post::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'author_id' => $author->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'body' => $data['body'],
                    'status' => 'published',
                    'published_at' => now()->subDays(7 * ($i + 1)),
                    'seo_title' => $data['seo_title'],
                    'seo_description' => $data['seo_description'],
                ]
            );
            $post->categories()->sync($categories->only($data['categories'])->pluck('id'));
            $post->tags()->sync($tags->only($data['tags'])->pluck('id'));
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function posts(): array
    {
        return [
            [
                'slug' => 'how-to-brief-a-video-production-team',
                'title' => 'How to brief a video production team (so the film you get is the film you imagined)',
                'excerpt' => 'A great brief is not a shot list — it is a decision about what the film must achieve. Here is the structure we wish every client used.',
                'seo_title' => 'How to Brief a Video Production Team — A Practical Guide',
                'seo_description' => 'What to put in a video production brief: objective, audience, single message, references, constraints and success metrics — with examples you can copy.',
                'categories' => ['Production', 'Corporate'],
                'tags' => ['pre-production', 'planning'],
                'body' => <<<'HTML'
<p>Most disappointing films can be traced back to the same moment: the brief. Not the shoot, not the edit — the brief. When a production team guesses what you want, you get their guess back in 4K.</p>
<h2>Start with the decision, not the video</h2>
<p>Before any talk of style or duration, answer one question: what should the viewer <em>do</em> after watching? Book a call, trust the brand, apply for a job, feel proud of the company they work for. One film, one job. Films briefed to do three jobs usually do none.</p>
<h2>The six things every brief needs</h2>
<ul>
<li><strong>Objective</strong> — the single action or feeling the film must produce.</li>
<li><strong>Audience</strong> — who is watching, on what platform, in what mindset.</li>
<li><strong>One message</strong> — if the viewer remembers a single sentence, which one?</li>
<li><strong>References</strong> — two or three links with a note on <em>what</em> you like in each: the pace, the tone, the grade. "Make it like this" without the why transfers nothing.</li>
<li><strong>Constraints</strong> — brand guidelines, people who must appear, locations, dates, approval chain.</li>
<li><strong>Success</strong> — how you will judge the result three months later.</li>
</ul>
<blockquote>A brief is not paperwork. It is the cheapest edit you will ever make — changes cost nothing while they are still words.</blockquote>
<h2>What to leave out</h2>
<p>Shot lists, camera models, and edit instructions. Those are the production team's craft — and prescribing them early locks the team out of solving your problem better than you imagined it. Share the destination, let the crew pick the road.</p>
<h2>The kickoff conversation</h2>
<p>A written brief plus a one-hour conversation beats either alone. The questions a good team asks in that hour — budget honesty, real deadlines, who signs off — are the ones that prevent the expensive surprises later. It is the hour every <a href="/services/videography">film we produce</a> starts with.</p>
HTML,
            ],
            [
                'slug' => 'wedding-photography-timeline-planning',
                'title' => 'Planning your wedding photography timeline: a working template',
                'excerpt' => 'Ceremonies run late, light disappears, and the couple ends up rushed through the photos that mattered most. A realistic timeline prevents all three.',
                'seo_title' => 'Wedding Photography Timeline — How to Plan Your Day',
                'seo_description' => 'How much time to allow for getting ready, couple portraits, family photos and golden hour — a realistic wedding photography timeline you can adapt.',
                'categories' => ['Weddings'],
                'tags' => ['planning'],
                'body' => <<<'HTML'
<p>Nobody remembers a wedding that ran twenty minutes late. Everybody remembers portraits shot in harsh noon sun because the timeline said so. The timeline is invisible when it works and unmissable when it fails.</p>
<h2>Work backwards from the light</h2>
<p>The best portrait light of the day is the hour before sunset. Fix that slot first — twenty to thirty minutes is enough for relaxed couple portraits — and build the rest of the day around it. Everything else can move; the sun cannot.</p>
<h2>Realistic durations</h2>
<ul>
<li><strong>Getting ready</strong> — 60–90 minutes per side. The last 30 minutes produce the best frames: details done, emotions up.</li>
<li><strong>Family groups</strong> — 4–5 minutes per combination. Ten combinations means close to an hour. Cut the list, not the time per photo.</li>
<li><strong>Couple portraits</strong> — two short sessions beat one long one: twenty minutes in the afternoon, twenty at golden hour.</li>
<li><strong>Buffer</strong> — 15 minutes of slack per major transition. Indian weddings especially: baraat timing is a hope, not a schedule.</li>
</ul>
<h2>Assign a wrangler</h2>
<p>The single biggest time-saver is one designated person — not the couple, not a parent — who knows every name on the family photo list and can pull people out of the cocktail hour. Photographers can direct a pose; they cannot find your uncle.</p>
<blockquote>Plan the day so that photography fits inside the celebration — never the other way round. The best photos happen when nobody feels photographed.</blockquote>
<h2>Share the plan early</h2>
<p>Send your photography team the full event schedule two weeks out. They will spot the collisions — a sunset portrait slot during speeches, a first look scheduled in a lobby — while there is still time to fix them. It is the first thing we ask for on every <a href="/industries/wedding-pre-wedding">wedding we cover</a>.</p>
HTML,
            ],
            [
                'slug' => 'what-post-production-actually-includes',
                'title' => 'What post-production actually includes (and why it is half the film)',
                'excerpt' => 'Edit, grade, sound, conform, masters — a plain-language tour of everything that happens between the shoot and the file you receive.',
                'seo_title' => 'What Does Video Post-Production Include? A Full Breakdown',
                'seo_description' => 'A plain-language guide to video post-production: editing, color grading, sound design, conform and delivery masters — and why each stage matters.',
                'categories' => ['Post-production'],
                'tags' => ['editing'],
                'body' => <<<'HTML'
<p>Clients see the shoot: lights, cameras, a crew in motion. What they rarely see is that an equal share of the film is built afterwards, in dark rooms, by people staring at waveforms. Here is what actually happens in post.</p>
<h2>The edit — structure</h2>
<p>Editing is writing with footage. The editor finds the story's spine, kills good shots that serve no purpose, and controls rhythm — when to breathe, when to cut hard. A rough cut answers "is the story right?"; a fine cut answers "is every frame earning its place?"</p>
<h2>The grade — look</h2>
<p>Color grading happens in two passes. Correction first: every shot matched, skin tones true, exposure consistent — the invisible work. Then the look: the palette and contrast that give the film its character. A film that "feels expensive" is usually a film that was graded well.</p>
<h2>Sound — the half you feel</h2>
<p>Dialogue cleaned, rooms matched, music mixed so it lifts rather than drowns, and the small designed details — a door, footsteps, air — that make picture feel real. Audiences forgive soft focus; they never forgive bad audio.</p>
<blockquote>Watch any film you love with the sound off, then with picture off. You will discover which half was doing the heavy lifting.</blockquote>
<h2>Conform and masters</h2>
<p>The finishing pass: graphics and titles in place, final resolution conform, loudness standards met, and exports tuned per destination — a bright punchy master for Instagram, a broadcast-safe one for TV, a clean archival master for the future re-cut you do not know you need yet.</p>
<h2>Why in-house post matters</h2>
<p>When the people who shot the film also finish it, intent survives. The lighting choices made on set were made <em>for</em> a grade someone already had in mind. Outsourced post starts from zero; integrated post starts from the brief. That pipeline — edit, grade, sound and conform under one roof — is what our <a href="/services/post-production">post-production service</a> runs, including post-only projects on footage from any camera.</p>
HTML,
            ],
            [
                'slug' => 'photo-vs-video-corporate-event-coverage',
                'title' => 'Photo, video, or both? Choosing coverage for your corporate event',
                'excerpt' => 'Different outputs serve different jobs: photos fill channels for months, film carries the story. How to decide where your budget works hardest.',
                'seo_title' => 'Corporate Events: Photography vs Video Coverage',
                'seo_description' => 'How to choose between photography, videography or both for a corporate event: outputs, team sizes, on-site edits and how each asset gets used.',
                'categories' => ['Corporate', 'Production'],
                'tags' => ['events', 'planning'],
                'body' => <<<'HTML'
<p>The honest answer to "photo or video?" is another question: what happens to the coverage after the event? Assets with no destination are decoration. Assets with a job are marketing.</p>
<h2>What photography does best</h2>
<p>Volume and speed. A single conference day yields hundreds of usable frames — speaker portraits for LinkedIn, crowd energy for next year's ticket page, sponsor logos in context for renewal decks. Photos are the fastest asset to publish and keep feeding channels for months.</p>
<h2>What film does best</h2>
<p>Feeling and proof. A two-minute highlight film carries the atmosphere of the event to everyone who was not in the room — future attendees, sponsors, leadership. Keynote capture turns a one-time talk into an evergreen content library.</p>
<h2>The on-site edit</h2>
<p>For multi-day events, when the schedule allows it, a highlight reel cut on-site and screened before the closing session is the single highest-impact deliverable: the room watches itself, shares it that evening, and registration links ride the wave while attention is at its peak.</p>
<blockquote>Photos are how an event is remembered by the people who came. Film is how it is experienced by the people who did not.</blockquote>
<h2>A practical split</h2>
<ul>
<li><strong>Internal town halls</strong> — photography plus keynote capture. Skip the highlight film.</li>
<li><strong>Client-facing conferences</strong> — both, with an on-site edit if the event runs more than one day and the schedule allows.</li>
<li><strong>Product launches</strong> — film-led; the launch film outlives the evening. Photography for press and social.</li>
</ul>
<h2>One brief, one team</h2>
<p>Whatever the mix, put stills and motion under one brief and, ideally, one team. Separate vendors compete for the same angles at the same moments; an integrated crew choreographs around each other — which is how we run <a href="/industries/corporate-shoots">corporate event coverage</a>.</p>
HTML,
            ],
            [
                'slug' => 'preparing-your-team-for-a-corporate-shoot',
                'title' => 'How to prepare your team for a corporate shoot',
                'excerpt' => 'Most people dread being on camera. Preparation — the right kind — is the difference between stiff footage and a team that looks like itself.',
                'seo_title' => 'How to Prepare Your Team for a Corporate Shoot',
                'seo_description' => 'Wardrobe guidance, interview prep, location readiness and scheduling tips that make corporate shoot days calm — and the footage natural.',
                'categories' => ['Corporate'],
                'tags' => ['pre-production', 'events'],
                'body' => <<<'HTML'
<p>The most expensive thing on a shoot day is discomfort. It slows every setup, stiffens every interview, and no amount of grading fixes a face that wants to be somewhere else. Preparation is how you buy ease.</p>
<h2>Tell people why, not just when</h2>
<p>A calendar invite that says "Video shoot, 2 PM" produces dread. Two paragraphs on what the film is for, what will be asked, and how long it takes produces volunteers. People perform better inside a story they understand.</p>
<h2>Wardrobe: simple rules</h2>
<ul>
<li>Solid mid-tone colors beat patterns — fine stripes and small checks shimmer on camera.</li>
<li>Bring a second option; the crew will pick against the backdrop.</li>
<li>Avoid brand-new clothes. People sit like themselves in clothes they trust.</li>
</ul>
<h2>Interviews: prepare the person, not the answers</h2>
<p>Share the question areas a few days ahead — never a script. Memorized answers sound memorized. The best on-camera moments come from someone talking about work they genuinely care about to one person behind the lens, not "addressing the audience."</p>
<blockquote>Nobody needs media training to talk about the thing they are proud of. The interviewer's job is just to get them there.</blockquote>
<h2>Ready the space</h2>
<p>A shoot moves furniture, kills overhead lights, and occupies a room for longer than feels reasonable. Book the space for the whole block, warn the neighbours about noise, and nominate one person who can say yes to small questions — the crew will have twenty of them before lunch.</p>
<h2>Schedule around energy</h2>
<p>Put interviews in the morning while faces are fresh. Save b-roll — people working, walking, meeting — for the afternoon slump when nobody has to speak. And feed the crew with the team: shoots run on the same fuel as everything else. The full run-of-day is on our <a href="/industries/corporate-shoots">corporate shoots</a> page.</p>
HTML,
            ],

            // The two pillar posts behind the keyword map's Tier 1 clusters. The
            // five posts above are craft notes; these two aim at commercial
            // queries the site already registers for ("video post production",
            // Search Console positions 57–64, with no supporting content).
            //
            // The cost guide quotes the budget bands from the studio's own quote
            // wizard rather than invented market rates — the one honest source
            // of numbers available, and the transparency itself is the point:
            // no local competitor publishes any.
            [
                'slug' => 'video-editing-cost-india',
                'title' => 'What drives the cost of video editing in India',
                'excerpt' => 'Every serious edit is quoted to its scope, not off a rate card. The six things that decide the number — so you can predict your own quote before the first call.',
                'seo_title' => 'Video Editing Cost in India — What Actually Drives the Price',
                'seo_description' => 'What decides the cost of video editing and post-production in India: the six drivers a studio quotes from, post-only projects, where cutting budget backfires, and how to get an accurate quote fast.',
                'categories' => ['Post-production'],
                'tags' => ['editing', 'planning'],
                'body' => <<<'HTML'
<p>There is no honest rate card for video editing, because two films with the same runtime can be entirely different amounts of work. What a serious studio does instead is quote each project against six drivers — and once you know them, you can predict where your own quote will land before you ever get on a call.</p>
<h2>What moves the number</h2>
<p><strong>Footage volume.</strong> An editor's first job is watching. Two hours of rushes and twenty hours of rushes are different projects even if the final film is ninety seconds in both cases — a multi-day event shot on three cameras costs more to cut than a single-location interview, before any creative decision is made.</p>
<p><strong>Edit complexity.</strong> A talking-head with b-roll is assembly; a narrative brand film is writing with footage. The more the structure has to be <em>found</em> in the material rather than planned before it, the more edit rounds the film needs.</p>
<p><strong>The grade.</strong> Colour correction — matching shots, fixing exposure, holding skin tones — is included in any serious edit. A <em>look</em>, built in DaVinci and reviewed on a calibrated display, is its own line. It is also, frame for frame, the biggest visible difference between a film that feels expensive and one that does not.</p>
<p><strong>Sound.</strong> Cleaned dialogue, a licensed track, a real mix. Audiences forgive soft focus; they never forgive bad audio — and sound is the half of post most often cut from a budget and most regretted afterwards.</p>
<p><strong>Motion graphics.</strong> Titles and lower thirds are standard. Animated diagrams, logo builds and data visualisation are designed, not applied — they price like design work because they are design work.</p>
<p><strong>Revision rounds.</strong> The quiet budget-killer. Two consolidated feedback rounds are typical; per-stakeholder drip feedback can double an edit. One person collecting all notes into one document is worth real money.</p>
<h2>Post-only projects</h2>
<p>You do not need to have shot with a studio for it to finish your film. We take <a href="/services/post-production">post-only projects</a> — footage from any camera, another crew's rushes, an archive — and run the same pipeline: edit, grade, sound, conform. If you already have material sitting on a drive, the cost conversation starts at the edit, not the shoot.</p>
<h2>Where cutting budget backfires</h2>
<p>Skipping the grade shows in every frame. Skipping the mix shows in every second. Cutting revision structure — going cheap on project management — usually costs more than either, because an edit that drifts through six rounds of scattered feedback burns the hours a look and a mix would have taken. If the budget is genuinely tight, cut scope: a shorter film finished properly beats a longer one abandoned at the rough cut.</p>
<h2>Getting your number</h2>
<p>A useful quote needs four facts: what the footage is (or what the shoot is), what the film must achieve, where it will run, and when it is needed. Bring those and the number arrives in one conversation, not a week of back-and-forth — <a href="/contact">send us the four facts</a> and you will have a reply with a direction and a number. If you want the fuller picture of what you are buying first, <a href="/blog/what-post-production-actually-includes">what post-production actually includes</a> walks the whole pipeline.</p>
HTML,
            ],
            [
                'slug' => 'corporate-event-video-coverage-guide',
                'title' => 'Corporate event video: planning coverage for a summit, AGM or launch',
                'excerpt' => 'The agenda will not stop for the crew. How coverage is actually planned — cameras, audio, permissions and deliverables — for events that happen exactly once.',
                'seo_title' => 'Corporate Event Video Coverage — A Planning Guide',
                'seo_description' => 'How to plan video coverage for a corporate summit, AGM or launch: camera positions, desk audio with backup, permissions, and the deliverables to ask for — from a crew that shoots them.',
                'categories' => ['Corporate', 'Production'],
                'tags' => ['events', 'planning'],
                'body' => <<<'HTML'
<p>A corporate event is a film with no second take. The keynote happens once, the CEO has eleven minutes between sessions, and the agenda does not stop because a camera was in the wrong place. Every good event film is therefore won in planning — the shoot is just the plan executing.</p>
<h2>Start from the run-of-show</h2>
<p>Coverage is planned off your agenda, not off a shot list a crew invented. Send the run-of-show early and mark the moments that matter: the announcement, the award, the handshake that took two years of negotiation. A crew that knows the third speaker is the story positions for the third speaker.</p>
<h2>The camera plan</h2>
<p>The reliable minimum for a stage event is three roles: a locked wide that never misses, a tight camera on faces, and a roving camera on the audience — because the room reacting <em>is</em> the story of a good event, and the moment you most need that shot is the moment you cannot stage it. Bigger rooms scale the same roles up rather than adding new ones.</p>
<h2>Audio is the actual risk</h2>
<p>The single most common way corporate footage is lost is sound: a crew that pointed a camera microphone at a PA system. Insist on a feed from the sound desk plus an independent backup recorder. It is one line in an email to your AV vendor, and it is the difference between a keynote you can publish and a keynote you can only caption.</p>
<h2>Permissions, before the day</h2>
<p>Three questions to settle in advance: can the crew shoot the audience (some rooms carry attendees whose employers say no), can slides appear on camera (legal review cares), and who signs off the final cut. Answering these on the day costs shooting time; answering them a week early costs nothing.</p>
<h2>Ask for deliverables by shelf life</h2>
<p>The highlight film matters most in the week after the event, while the room still talks about it — days, not weeks, is the right expectation. Full-session recordings, chaptered, live longer on the intranet. Vertical cuts with subtitles carry the event to people who were never coming. Agree the set before the shoot, because coverage decisions on the day depend on it: a crew cutting verticals frames differently from one cutting only a wide film.</p>
<p>How we run this — positions, redundancy, review gates — is on our <a href="/industries/corporate-shoots">corporate shoots</a> page. And if you are still deciding what to book, <a href="/blog/photo-vs-video-corporate-event-coverage">photo, video or both</a> covers the trade-off, while <a href="/blog/preparing-your-team-for-a-corporate-shoot">preparing your team</a> is the checklist to circulate internally once you have.</p>
HTML,
            ],
        ];
    }
}
