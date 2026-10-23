<?php

/**
 * Copy for the per-city service pages (service x city, nine rows).
 *
 * Why this file exists: each service gets a landing page per Delhi NCR city,
 * and a page that only swaps the city name is a doorway page. Every row here is
 * written separately, around what that service actually looks like in that
 * city, so the pages stand on their own text.
 *
 * It is seeded create-only (keyed on service_slug + city_slug), so edits made in
 * the admin survive a deploy. Changing this file therefore never overwrites a
 * live page; edit the row in the admin, or delete it there and re-seed.
 *
 * Copy rules: no prices, no time promises, no claims about past projects or
 * clients in a city. Capabilities come from ServicesSeeder only.
 */

return [
    [
        'service_slug' => 'videography',
        'city_slug' => 'delhi',
        'city_name' => 'Delhi',
        'headline' => 'Videography in Delhi for Events and Brand Films',
        'intro' => '<p>TheLastClicks provides videography in Delhi for companies, hotels and event organisers that need a film made properly, from the first treatment to the final export. Our crews travel from the studio in Sector 26, Noida, with cameras, sound and a producer who handles the logistics.</p><p>Delhi shoots rarely fail on camera work. They fail on access: a hall that restricts rigging, a lawn that needs approval, a delegation that moves on its own schedule. We plan around those limits before anyone arrives, so the film reflects the day rather than the paperwork behind it.</p><p>Below is how we approach venues, permissions, brand films and delivery for Delhi clients.</p>',
        'sections' => [
            [
                'heading' => 'Planning a shoot around Bharat Mandapam and Yashobhoomi',
                'body' => '<p>Large convention venues come with rules about rigging, power, camera positions and where tripods may stand. We ask for these at the brief, not on the morning of the event, because they decide how many cameras the hall can really take.</p><p>LED walls add another variable. They throw coloured light across faces and skin tones, so we test camera settings against the screen content during set-up.</p><p>For conferences and launches we send a unit of two to six cameras with an on-site producer, who keeps the crew clear of dignitary movement and stage cues. Recap reels can be cut on site while the programme is still running.</p><p>Government and institutional events add protocol beyond venue rules. Seating plans change, security checks slow load-in, and some areas are off limits to cameras. A producer who has read the run of show can place operators clear of guests. The same producer briefs them on moments that cannot be repeated, such as a lamp lighting or a keynote.</p>',
            ],
            [
                'heading' => 'Hotel ballrooms, Aerocity and leadership dinners',
                'body' => '<p>A ballroom mixes warm chandeliers, cool stage lights and shiny marble floors. Left alone, that combination gives uneven colour and distracting reflections.</p><p>We decide the lighting plan during pre-production, then pick lens positions that avoid the worst glare. For speeches and panels we feed clean audio from the desk instead of relying on camera microphones.</p><p>Hospitality launches, award dinners and private celebrations follow the same logic, and event videography in Delhi hotels always starts with a site visit to the room. If you are planning a celebration film, our <a href="/industries/wedding-pre-wedding">wedding and pre-wedding work</a> follows the same treatment-first method.</p>',
            ],
            [
                'heading' => 'Permits and approvals for heritage and institutional locations',
                'body' => '<p>Central Delhi has plenty of striking locations, and most of them need permission before a camera comes out. Monuments, government precincts and embassy-adjacent streets each have their own approval route.</p><p>We treat permission as a pre-production step. A location scout and a written treatment go first, so the request describes exactly what we will film, with how many people and how much kit.</p><p>Timing matters too. Permission often covers specific hours and specific areas, and a busy street in Connaught Place or the Lutyens zone has its own rhythm. We build the schedule around the approved window. The crew stays small so it can move quickly.</p><p>Approval can still be refused. That is why every treatment carries a fallback location with a similar look, and why we never promise a frame we have not cleared.</p>',
            ],
            [
                'heading' => 'Brand films and leadership stories for head offices',
                'body' => '<p>A brand film starts with a written treatment built around one emotional beat. That single idea keeps the interviews, the b-roll and the music pulling in the same direction.</p><p>Our director, DOP and editor work from the same brief, so the person cutting the film knows why each shot was taken. Grading, sound and conform stay in-house.</p><p>For founders and senior leaders, we prepare the questions in advance and shoot in the office they actually work from. The result sounds like a person, not a press release.</p><p>If you are weighing corporate video production in Delhi against a quick phone-shot clip, the difference is in the planning. A brand film carries a point of view, and every shot has to earn its place in the cut.</p>',
            ],
            [
                'heading' => 'One shoot, every screen your audience uses',
                'body' => '<p>The same footage often has to play on a stage screen, a LinkedIn feed and a WhatsApp forward. We deliver a hero film plus 9:16, 1:1 and 16:9 cuts, framed for each from the start.</p><p>Subtitles and Hindi and English versions are available on request. Sound is finished to broadcast-compliant standards and graded in ACES, so colour holds up across devices.</p><p>Planning the formats before the shoot matters. A shot framed for 16:9 can lose its subject when cropped to 9:16, so we compose key moments with the tighter frames in mind.</p><p>Feedback runs through timecoded review links across three rounds: rough, fine and final. If the footage came from another team, our <a href="/services/post-production">post-production service</a> can cut and finish it.</p>',
            ],
            [
                'heading' => 'What shapes the cost of a Delhi shoot',
                'body' => '<p>No two quotes look alike, because the work behind them differs. These factors move the estimate most:</p><ul><li>Crew size and number of cameras</li><li>Number of shoot days and locations</li><li>Permits, access and venue requirements</li><li>Length and number of final deliverables</li></ul><p>A single-hall conference with one speaker and one cut needs far less than a multi-day summit with parallel tracks. We would rather explain that openly than hide it behind a package.</p><p>Tell us the venue, the audience and the formats you need through our <a href="/contact">contact page</a>, and we will scope your videography in Delhi properly.</p>',
            ],
        ],
        'highlights' => [
            'Event units of 2 to 6 cameras with an on-site producer',
            'Recap reels can be cut on site during the event',
            'Hero film plus 9:16, 1:1 and 16:9 cuts',
            'Grade, sound and conform finished in-house',
            'Studio in Sector 26, Noida; crews travel to Delhi',
            'Three review rounds with timecoded links',
        ],
        'faqs' => [
            [
                'q' => 'How do venue rules at Bharat Mandapam or Yashobhoomi affect a shoot?',
                'a' => '<p>They shape camera positions, rigging, power access and what support gear is allowed. We ask for the venue rulebook and floor plan during the brief, then build the camera plan around them. Our producer handles clearances on the day, so the crew can focus on the programme. If a position is not allowed, we plan an alternative before the event.</p>',
            ],
            [
                'q' => 'Do you write the script and concept, or do we need to bring one?',
                'a' => '<p>You can bring a script or start with only a goal. We work treatment-first: a short written concept with one emotional beat, which you approve before any shoot is booked. Our <a href="/blog/how-to-brief-a-video-production-team">guide to briefing a video team</a> lists what to prepare. The treatment then guides the shoot and the edit.</p>',
            ],
            [
                'q' => 'How is a video production team different from a freelancer or an in-house team?',
                'a' => '<p>You get a director, DOP and editor working from one brief, plus a producer for logistics. Grading and sound are finished in the same studio, so there are fewer handovers. A freelancer or an in-house team can be a good fit for small jobs. Multi-camera events and brand films usually need the wider crew.</p>',
            ],
            [
                'q' => 'What video formats do we get for stage screens, LinkedIn and WhatsApp?',
                'a' => '<p>We deliver a hero film plus 9:16, 1:1 and 16:9 cuts, each framed for its platform. Subtitles and localisation passes are available on request. Masters are prepared per platform, with licensed music and broadcast-safe sound. Tell us where the film will play, and we will confirm the exact specifications.</p>',
            ],
        ],
        'meta_title' => 'Videography in Delhi: Events & Brands',
        'meta_description' => 'Videography in Delhi for events, hotels and brand films. Venue-ready crews, multi-camera units and in-house finishing. Talk to our Noida studio.',
    ],
    [
        'service_slug' => 'videography',
        'city_slug' => 'noida',
        'city_name' => 'Noida',
        'headline' => 'Videography in Noida for Factories and Companies',
        'intro' => '<p>TheLastClicks offers videography in Noida for manufacturers, IT teams and exhibitors, and our studio sits on Sector 26. The director, camera crew and editor work from one brief, in one place, so a question about the cut goes to the person who made it.</p><p>Most Noida work falls into three kinds: process films on live production lines, culture and leadership films in working offices, and coverage of trade shows. Each has its own constraints, such as noise, restricted zones or crowded halls.</p><p>Here is how we plan each one, and what we need from you before videography in Noida begins on site.</p>',
        'sections' => [
            [
                'heading' => 'Filming factory floors and production lines in Greater Noida',
                'body' => '<p>A plant film is strongest when it follows the process: raw material in, finished product out. We begin with a walk-through led by your operations head, who marks restricted areas and safe positions.</p><p>Safety rules decide the schedule. We plan shoots around machine cycles, shift changes and noisy sections, and we do not ask a line to stop for a shot unless you agree to it.</p><p>The frames that carry a plant film usually show people and quality checks, not just machinery. We build those into the treatment so the film feels like your factory, not a generic one.</p><p>Greater Noida plants often run several lines at once. We list the stations we want to show and rank them. The essential ones go first, in case access narrows later in the visit.</p>',
            ],
            [
                'heading' => 'Corporate and culture films for Sector 62 and 63 offices',
                'body' => '<p>Filming in a working office means borrowing space without disrupting teams. We agree on a few free rooms and a quiet window for interviews, then shoot around meetings.</p><p>Leadership interviews work better with questions shared in advance. Role-based clips, where each team explains what it does, make strong material for hiring pages and onboarding.</p><p>Sound deserves attention on open-plan floors. We record interviews with lavalier microphones and keep a room-tone track, so edits cut cleanly without a change in background noise.</p><p>Our <a href="/industries/corporate-shoots">corporate shoots</a> follow the same approach. To prepare employees who are nervous on camera, share our <a href="/blog/preparing-your-team-for-a-corporate-shoot">guide to preparing your team</a> beforehand.</p>',
            ],
            [
                'heading' => 'Trade shows and exhibitions at India Expo Mart',
                'body' => '<p>An expo hall is loud, crowded and constantly changing. We split the unit across pavilions, with one operator on your stand and others on keynotes, demos and visitor moments.</p><p>Interviews need a quieter corner, so we scout one on the first visit and record ambient sound separately. A producer coordinates the camera operators and keeps your schedule in view.</p><p>Highlights can be cut on site while the show is still running, ready for your social channels.</p><p>Exhibitor teams should brief us on stand layout, scheduled demos and the people they want on camera. A one-page plan is enough. It lets the producer spot gaps before the hall opens.</p>',
            ],
            [
                'heading' => 'Delivery specs for Film City and broadcast teams',
                'body' => '<p>Media houses around Film City often need files that meet a broadcast checklist. We finish sound to broadcast-compliant standards and prepare masters to the platform or channel specification you provide.</p><p>Send us the delivery document before the shoot, not after the edit. It tells us the frame rate, audio levels and file types to plan for from day one. Corporate teams who share this with us avoid re-exports later.</p>',
            ],
            [
                'heading' => 'A Sector 26 studio where director and editor share one floor',
                'body' => '<p>Because the studio is local, you can walk in to review a rough cut, or meet the DOP before a recce. Nobody relays notes through a chain of vendors.</p><p>A local studio also makes pre-production easier. Treatments, location lists and shot plans can be discussed in person. A recce of your plant or stand needs no long trip.</p><p>Grade, sound and conform happen in-house, with ACES grading as the colour standard. Feedback runs in three structured rounds, rough, fine and final, through timecoded review links.</p><p>If you already hold footage from another shoot, we also edit it. Our write-up on <a href="/blog/what-post-production-actually-includes">what post-production includes</a> explains each stage.</p>',
            ],
            [
                'heading' => 'What shapes the cost of a Noida shoot',
                'body' => '<p>Cost follows effort, not just running time. A film of the same length can need very different resources. A short plant film with strict access rules can need more planning than a longer office interview.</p><ul><li>Crew size and equipment</li><li>Number of locations and shoot days</li><li>Safety, access and approvals</li><li>Cuts, subtitles and platform versions</li></ul><p>A single office interview in a quiet room is a smaller job than a plant film across several lines and shifts, even if both end up the same length on screen. We would rather talk through scope with you than guess.</p><p>Share your location and goals on the <a href="/contact">contact page</a>, and we will respond with a scoped plan.</p>',
            ],
        ],
        'highlights' => [
            'Studio on Sector 26, Noida; no travel for local teams',
            'Plant and process films on live production floors',
            'Event units with 2 to 6 cameras and a producer',
            'Broadcast-compliant sound and platform-spec masters',
            'Director, DOP and editor work from one brief',
            'Timecoded review links across three structured rounds',
        ],
        'faqs' => [
            [
                'q' => 'How do you film on a factory floor without stopping production?',
                'a' => '<p>We start with a walk-through with your operations head, who points out restricted zones and safe positions. Then we plan camera work around machine cycles and shift patterns. Safety briefings come first, and anyone on the crew follows your plant rules. Where a shot needs a pause, you decide whether it is worth it.</p>',
            ],
            [
                'q' => 'Can you cover a trade show or expo and cut a recap during the event?',
                'a' => '<p>Yes. For exhibitions we send a unit of two to six cameras with an on-site producer, who divides coverage between your stand, stage sessions and visitor interviews. A recap reel can be cut on site while the show continues, so your team can post highlights to its channels while attendees are still on the floor.</p>',
            ],
            [
                'q' => 'Do you shoot at our office, or only at your studio?',
                'a' => '<p>We shoot where the story is: offices, plants and expo halls. The Sector 26 studio is where the edit, grade and sound happen, and where you can review work in person. For interviews that need a controlled backdrop, a studio session is an option too. We confirm the plan once we know your goals.</p>',
            ],
            [
                'q' => 'Can you give us different cuts for LinkedIn, sales decks and screens?',
                'a' => '<p>We deliver a hero film plus 9:16, 1:1 and 16:9 versions, so the same shoot serves a feed, a sales presentation and a lobby screen. Subtitles and localisation passes are available on request. Let us know where each cut will be used, and we will frame the footage accordingly.</p>',
            ],
        ],
        'meta_title' => 'Videography in Noida: Corporate Films',
        'meta_description' => 'Videography in Noida for factories, offices and expos, with a local Sector 26 studio. Plan your shoot with a team that edits and grades in-house.',
    ],
    [
        'service_slug' => 'videography',
        'city_slug' => 'gurugram',
        'city_name' => 'Gurugram',
        'headline' => 'Videography in Gurugram for Corporates and Launches',
        'intro' => '<p>TheLastClicks provides videography in Gurugram for MNC offices, SaaS teams and retail brands that need films cleared by several stakeholders. Crews travel from our Sector 26, Noida studio and handle building access, interviews and multi-format delivery for videography in Gurugram.</p><p>Gurgaon work is usually less about one big day and more about coordination. Building management wants an equipment list, marketing wants a launch cut, and legal wants to see every version before it goes live.</p><p>This page explains how we plan for towers, launches, approvals, retail floors and outdoor celebrations, and what we need from your side before the crew arrives.</p>',
        'sections' => [
            [
                'heading' => 'Filming inside Cyber City towers and MNC offices',
                'body' => '<p>Managed towers have visitor protocols. Building management typically asks for an equipment list, names for the access pass and a time slot for the lift. We send these ahead so the crew is not held at reception.</p><p>Glass walls cause reflections that ruin interviews, so we scout the floor and place subjects away from windows and screens.</p><p>Employer-brand films land when employees speak in their own words. We build the treatment around real roles and skip the staged handshakes and laptop clichés.</p><p>Many offices here are shared-service centres with large teams and tight floor plans. We agree on one or two filming zones and keep the crew to the numbers building security has approved. That way, normal work around the shoot carries on.</p>',
            ],
            [
                'heading' => 'Launch films for startups and SaaS teams in Udyog Vihar and Golf Course Road',
                'body' => '<p>Product launches need founder interviews that sound natural. We share the questions early, so the founder arrives with answers rather than a script.</p><p>Then we shoot once and cut for every channel. A 16:9 film for the website, a 9:16 cut for stories and a 1:1 version for feeds all come from the same session.</p><p>If you are unsure what to prepare, our <a href="/blog/how-to-brief-a-video-production-team">briefing guide for video teams</a> is a good place to start.</p><p>Corporate video production in Gurgaon for a product launch often includes screen recordings and interface shots as well. Send us the demo environment in advance so we can plan how those clips sit beside the interviews.</p>',
            ],
            [
                'heading' => 'Sign-off across marketing, legal and HR',
                'body' => '<p>Corporate films collect opinions. When reviewers sit in different offices, scattered email feedback slows everything down.</p><p>We share timecoded review links, so every comment attaches to a frame. Versions are numbered, and feedback runs in three rounds: rough, fine and final.</p><p>Agree early on who has the final say. A single decision-maker per round keeps the edit moving.</p><p>Legal and HR reviewers often need to check claims, names and job titles on screen. We keep graphics and subtitles editable until the final round, so a corrected name does not mean starting over. This is the step most corporate teams underestimate, and it is why we plan review as carefully as the shoot.</p>',
            ],
            [
                'heading' => 'Mall launches and retail activations',
                'body' => '<p>Retail events happen in public space, so permission from centre management comes before the shoot. We confirm filming areas, load-in routes and any restrictions near other tenants.</p><p>A multi-camera unit with a producer covers the entrance, the activity zones and the crowd. Recap reels can be cut on site for social posts while the event is still running.</p><p>Wondering whether you need stills as well? Our note on <a href="/blog/photo-vs-video-corporate-event-coverage">photo versus video at corporate events</a> helps you decide.</p><p>Mall filming also needs a sensible plan for audio. Music in the atrium, announcements and crowd noise all compete with voices, so we record interviews in a quieter spot and rely on separate ambient tracks for atmosphere.</p>',
            ],
            [
                'heading' => 'Farmhouse lawns and hotel banquets, filmed for light',
                'body' => '<p>Outdoor celebrations depend on the sun. We plan ceremonies and portraits around sunset and open shade, and we scout the lawn for power and backup cover.</p><p>Banquet halls are the reverse: the decorator has already designed the lighting, so we work with it rather than against it. Colour temperature shifts as lamps and daylight mix, and we correct that in the grade.</p><p>Guests move around, children run into frame and speeches start late. A producer who stays in contact with the planner helps the crew arrive at each moment ready, with a camera already on the right person.</p><p>Families planning an event can look at our <a href="/industries/wedding-pre-wedding">wedding and pre-wedding work</a> for the style we bring.</p>',
            ],
            [
                'heading' => 'What goes into a Gurugram quote',
                'body' => '<p>We price effort, and effort depends on a few practical answers. A launch film for one product and a series for several regional teams are very different projects, even in the same tower.</p><p>The questions that matter most:</p><ul><li>How many people are on the crew?</li><li>How many days and locations are involved?</li><li>Which cuts and languages do you need?</li></ul><p>Grade and sound happen at our Noida studio, which keeps the finishing consistent across every cut. Send the details through the <a href="/contact">contact form</a> and we will reply with a plan.</p>',
            ],
        ],
        'highlights' => [
            'Crews travel from our Sector 26, Noida studio',
            'Treatment-first: one message per film',
            'One shoot, cut for 16:9, 9:16 and 1:1',
            'ACES grading and sound finished in-house',
            'Timecoded review links for multi-office sign-off',
            'Retail launches covered by multi-camera unit with producer',
        ],
        'faqs' => [
            [
                'q' => 'Can you shoot inside our office tower, and what do you need from building management?',
                'a' => '<p>Yes. We usually need approval for the equipment list, names for access passes, loading routes and a booked meeting room. Send us the building rules early and we handle the rest. We also check glass walls and screens when scouting, so interviews avoid reflections that could cause reshoots.</p>',
            ],
            [
                'q' => 'Do you provide full production, or just editing?',
                'a' => '<p>Both. We can plan, shoot and finish a film end to end, or take footage you already hold and edit, grade and add sound. If another team shot it, send a sample first so we can check formats and quality. The process follows the same review rounds either way.</p>',
            ],
            [
                'q' => 'What deliverables do we get at the end of a shoot?',
                'a' => '<p>A hero film plus 9:16, 1:1 and 16:9 cuts, graded in ACES and mixed with broadcast-compliant sound. Music is fully licensed and broadcast-safe, with a custom score available. Subtitles and localisation passes can be added on request, and masters are prepared for each platform you name.</p>',
            ],
            [
                'q' => 'Can reviewers in different offices comment on the same cut?',
                'a' => '<p>Yes. We share frame-accurate, timecoded review links, so everyone comments on the same version. Feedback runs through three structured rounds: rough, fine and final. Collect comments from marketing, legal and HR before each round, and the next version comes back clean.</p>',
            ],
        ],
        'meta_title' => 'Videography in Gurugram for Corporates',
        'meta_description' => 'Videography in Gurugram for corporate offices, SaaS launches and retail events. Plan tower access and approvals with a crew that finishes in-house.',
    ],
    [
        'service_slug' => 'photography',
        'city_slug' => 'delhi',
        'city_name' => 'Delhi',
        'headline' => 'Photography Services in Delhi for Events and Institutions',
        'intro' => '<p>TheLastClicks provides photography services in Delhi for brands, event organisers and institutions, with crews that travel in from our studio in Sector 26, Noida. Delhi work tends to happen in places with rules: convention halls run by their own AV teams, hotel lobbies, offices behind security gates and monuments that need permission. The work starts well before the first frame.</p><p>We plan light, access and the shot list with you first, then deliver curated selects instead of a folder dump. This page explains how a professional photographer in Delhi should handle events, headshots, interiors and portraits, and what to settle before you book one.</p>',
        'sections' => [
            [
                'heading' => 'Conference and exhibition photography at large venues',
                'body' => '<p>Large venues run on schedules and house rules. At Bharat Mandapam or Pragati Maidan, the AV team usually sets the stage lighting, and it changes between sessions. We ask for a site visit and the run of show beforehand, so we know where speakers stand and which doors are off limits.</p><p>Coverage follows the programme rather than chasing moments:</p><ul><li>Wide room frames that show scale and attendance</li><li>Speaker portraits on fast primes, tuned to the stage light</li><li>Booths, networking and sponsor moments from a written shot list</li></ul><p>High-resolution bodies, the Sony A7R V and Canon EOS R5, let us crop tight for social posts and still print from the same file. If video is also planned, read <a href="/blog/photo-vs-video-corporate-event-coverage">how to split photo and video coverage at a corporate event</a>.</p>',
            ],
            [
                'heading' => 'Headshots for institutions, firms and embassies',
                'body' => '<p>Embassies, law firms, universities and corporate offices often need consistent portraits of dozens of people. We set up a mobile studio on your premises and hold the lighting identical from the first sitter to the last. A team page then reads as one team.</p><p>Formal settings need a lighter touch. Protocol offices, faculty rooms and boardrooms rarely have spare space, so we work from a corner with a compact kit and keep the footprint small. Each sitter gets a few frames and a quick look at the screen, which settles nerves and speeds up approval.</p><p>Dress guidance, backdrop and sitter order are agreed in advance. That keeps a long session moving and respects busy diaries. Our <a href="/industries/corporate-shoots">corporate shoots</a> often pair headshots with workplace and event photography.</p>',
            ],
            [
                'heading' => 'Hotel and hospitality interiors around Aerocity',
                'body' => '<p>Lobbies, banquet halls and suites are tall, mirrored and lit by mixed sources. A tilt-shift lens keeps vertical lines straight, so columns and doorways do not lean. We blend Profoto strobes with Aputure continuous light to hold window views without flattening the room.</p><p>Hotels set their own access rules, so we agree timing and guest-area limits with the property in advance. Detail frames, such as table settings and fixtures, go on the shot list so one set can serve a website, a brochure and a booking platform.</p><p>If the property is occupied, we work around guests and staff rather than asking for rooms to be cleared. Tell us which spaces matter most and we will sequence the day so those are photographed first.</p>',
            ],
            [
                'heading' => 'Editorial portraits and heritage locations',
                'body' => '<p>Stone colonnades and old-city lanes make strong backdrops, but they bring crowds and permissions. We scout the location, check what is allowed and plan the light for the slot you are given. Portraits are directed rather than snapped, with wardrobe and framing discussed beforehand.</p><p>Where a site needs formal approval, apply early, because that step sits outside any photographer control. We then shape the shot list around what you are cleared to do.</p><p>Crowds change the plan too. Early light and quiet corners help, and a short list of must-have frames keeps the session focused when time at the location is limited. Wardrobe that suits stone, sky and old walls photographs better than busy patterns.</p>',
            ],
            [
                'heading' => 'From shot list to curated selects and licensing',
                'body' => '<p>Every job runs in five phases: creative direction, pre-production, the shoot, selection and retouching. Pre-production covers scouting and kit lists. During the shoot we monitor frames live on a tethered screen, so you can flag issues on the day.</p><p>For selection you receive curated contact sheets through a secure review link, not thousands of near-duplicates. Retouching is in-house, with colour matched to your brand hex codes and exports prepared for print and web.</p><p>Files sit in a searchable archive for two years, RAWs are available on request, and we re-export to any specification without a new fee. A fuller explanation of <a href="/services/post-production">what post-production covers</a> sits on its own page.</p>',
            ],
            [
                'heading' => 'What drives the cost of a Delhi photo shoot',
                'body' => '<p>Cost for photography services in Delhi follows effort, not a rate we can quote from a search page. The main drivers are the number of shoot days, crew size, how many locations you need and how many finished images you expect.</p><ul><li>Venue access and set-up conditions</li><li>Number of people or spaces photographed</li><li>Retouching depth and export formats</li><li>Licensing scope for the final images</li></ul><p>Clear briefs reduce cost, because they remove guesswork on the day. Share yours on the <a href="/contact">contact page</a> and we will respond with a scoped proposal.</p>',
            ],
        ],
        'highlights' => [
            'Crews travel from our Sector 26, Noida studio',
            'Events, headshots, interiors and heritage portraits',
            'Sony A7R V and Canon EOS R5 camera bodies',
            'Mobile studio for office headshots',
            'Retouching matched to your brand hex codes',
            'RAWs on request, two-year searchable archive',
        ],
        'faqs' => [
            [
                'q' => 'What makes conference photography different from regular event photography?',
                'a' => '<p>Conference work depends on the room. An AV team controls the stage light, speakers follow a fixed programme and some areas are restricted. A conference photographer reads the schedule, visits the venue beforehand and shoots to a list of required frames, such as sponsor boards and speaker portraits. Everyday event photography is looser and reacts to whatever happens.</p>',
            ],
            [
                'q' => 'Is it better to hire a local photographer or one who travels to the venue?',
                'a' => '<p>Postcode matters less than preparation when you hire photography services in Delhi. Our studio is in Sector 26, Noida, and crews travel across NCR, including Delhi. A travelling team that has done a site visit, agreed access and written a lighting plan will serve you better than a nearby photographer working blind. Ask any candidate how they prepare.</p>',
            ],
            [
                'q' => 'Do I need permission to photograph at monuments or hotels in Delhi?',
                'a' => '<p>Often, yes. Monuments and protected sites can require formal approval, and hotels usually restrict tripods, lighting and guest areas. Permissions are issued by the site or property, not by the photographer, so raise them while the plan is still open. We help shape a shot list that fits whatever you are cleared to do.</p>',
            ],
            [
                'q' => 'Who owns the photos after a shoot, and can I reuse them?',
                'a' => '<p>Usage rights are agreed before the shoot, so you know where the images can run, such as web, print or campaigns. You receive the curated, retouched selects, and RAW files are available on request. If your needs grow later, such as a new campaign, tell us and we will confirm the licence in writing.</p>',
            ],
        ],
        'meta_title' => 'Photography Services in Delhi',
        'meta_description' => 'Photography services in Delhi for conferences, institutional headshots, hotel interiors and heritage portraits. Send your brief for a scoped proposal.',
    ],
    [
        'service_slug' => 'photography',
        'city_slug' => 'noida',
        'city_name' => 'Noida',
        'headline' => 'Photography Services in Noida: Product, Plant, Team',
        'intro' => '<p>TheLastClicks offers photography services in Noida from its own studio in Sector 26, shooting products, factory floors and teams for businesses in the city and Greater Noida. The local mix is commercial: brands that need catalogue imagery, IT firms that need consistent staff portraits, and manufacturers that need clear records of their plants.</p><p>Because the studio sits here, a product shoot happens on a controlled set, and a reshoot does not mean crossing the NCR. If you are comparing a professional photographer in Noida, this guide sets out what each type of job involves and what to prepare.</p>',
        'sections' => [
            [
                'heading' => 'Product photography on a controlled studio set',
                'body' => '<p>A product shoot is really two jobs. Marketplaces want the object isolated, evenly lit and unambiguous. A campaign wants mood, shadow and a world around the thing. We build them as separate setups from one hero lighting unit, so colour matches even though the intent differs.</p><p>The studio runs Profoto strobes and Aputure continuous lights, with medium format and Sony A7R V bodies for detail. Our <a href="/industries/product-shoot-creative">product shoot and creative work</a> covers packshots, campaign stills and generated imagery that is checked against the real product.</p>',
            ],
            [
                'heading' => 'Reflective goods, labels and colour accuracy',
                'body' => '<p>Glass, foil and gloss behave like mirrors, so the rig and the room appear in the frame unless the set keeps them out. We flag, tent and polarise as routine, and a clean pack can take more setups than the campaign frame beside it.</p><p>Label colour is the other trap. A brand red that looks right on screen can print wrong, so we shoot to a reference you supply rather than to what pleases the monitor.</p>',
            ],
            [
                'heading' => 'What a studio in Sector 26 changes about the workflow',
                'body' => '<p>With the set nearby, nobody loads vans for a cross-city shoot. Samples arrive, the lighting stays standing across a catalogue, and frames are reviewed live on a tethered screen as they are captured. Your brand or product team can approve a look before the next item goes on the set.</p><p>That matters for consistency. When fifty products share one lighting plan, they read as one range on your shop page. Added items or revised angles slot into the same setup instead of starting from scratch.</p><p>Studio days also suit work that needs patience: stacked packaging, tiny components, textured fabric or products that need styling between frames. A quiet, controlled room beats a busy location for that kind of detail.</p>',
            ],
            [
                'heading' => 'Plant and industrial documentation in Noida and Greater Noida',
                'body' => '<p>Manufacturing floors are regulated places. Before we arrive, we agree the safety briefing, protective gear, restricted zones and any permits with your plant team. Multi-day visits are normal for larger facilities.</p><p>Frames are composed for their end use: a brochure, an investor deck or a tender document. Wide shots show scale and layout, while process frames show people and machines at work. Ask for the end use up front, because it decides which angles earn a place on the list.</p><p>Equipment matters here. We bring strobes with sensible cable runs, and we keep lenses and bodies protected from dust and heat. Someone from your plant team should walk the route with us, so we never step into an unmarked hazard.</p>',
            ],
            [
                'heading' => 'Team portraits for IT offices in Sector 62 and 63',
                'body' => '<p>Growing IT and services firms see website photos date quickly. We bring a mobile studio to your office and keep lighting the same across hundreds of employees, so new joiners match the existing team page.</p><p>Hybrid teams are handled with several short sessions on different office days rather than one long day, and people who miss a slot can join the next one with identical lighting. Prepare your people with our guide to <a href="/blog/preparing-your-team-for-a-corporate-shoot">getting a team ready for a corporate shoot</a>.</p>',
            ],
            [
                'heading' => 'Stills for productions around Film City',
                'body' => '<p>Production houses and broadcasters nearby need stills too: unit photography, cast portraits, key art and behind-the-scenes sets. A portrait session can run on a studio day instead of a location shoot, and we work from your mood references.</p><p>Key art needs a clear brief. Share the look, the colour palette and any existing poster work, and we will agree lighting and wardrobe notes with the publicity or art team before the session. Final selects go back as web and print exports.</p>',
            ],
            [
                'heading' => 'Selects, retouching and what drives cost',
                'body' => '<p>Images go through curated selection, then in-house retouching, with skin work and colour matched to your brand hex codes. You can re-export any specification later without a new fee, and the archive stays searchable. See <a href="/services/post-production">what post-production actually covers</a>.</p><p>Cost for photography services in Noida depends on the number of products, setups per product, shoot days, retouching depth and licensing. Send your product list through the <a href="/contact">contact page</a> for a scoped proposal.</p><p>A short brief helps most. Include the number of products, the platforms you sell on and the sizes of the largest items, since those details set how many setups the day needs.</p>',
            ],
        ],
        'highlights' => [
            'Studio at Sector 26, Noida',
            'Packshots and campaign stills',
            'Profoto strobes and Aputure continuous light',
            'Plant shoots on manufacturing floors',
            'Mobile studio for Sector 62 and 63 offices',
            'Re-export to any spec at no re-fee',
        ],
        'faqs' => [
            [
                'q' => 'What is the difference between e-commerce product photography and campaign photography?',
                'a' => '<p>E-commerce images isolate the product on a clean background, evenly lit, to meet marketplace rules. Campaign images add styling, shadow and setting to sell a mood. They need different lighting and framing, so we build them as separate setups from one hero light. That keeps product colour consistent across the listing and the advert.</p>',
            ],
            [
                'q' => 'Can you shoot reflective products like glass bottles and foil packaging?',
                'a' => '<p>Yes. Reflective surfaces need the set built around them, with flags, tents and polarising used to keep the rig and the room out of the frame. Expect extra setups for a clean pack. Where a frame must be literally true to the object, we photograph it rather than generate it.</p>',
            ],
            [
                'q' => 'Do you photograph inside factories and manufacturing plants?',
                'a' => '<p>Our photography services in Noida include factory work and multi-day visits on manufacturing floors, within the rules each plant sets. We agree the safety briefing, protective equipment, restricted areas and permits with your team first. Frames are composed for the use you name, whether a brochure, an investor deck or a tender, so tell us the end use when you brief.</p>',
            ],
            [
                'q' => 'What should I send before a product shoot?',
                'a' => '<p>Send the physical packaging, the marketplace specifications you must meet and a colour reference for labels and logos. Add a short list of angles or props you want in the campaign frames. Having this at the pre-production call changes how each frame is lit, and saves rework later.</p>',
            ],
        ],
        'meta_title' => 'Photography Services in Noida',
        'meta_description' => 'Photography services in Noida for products, factory floors and teams, shot at our Sector 26 studio or on site. Send your brief for a scoped proposal.',
    ],
    [
        'service_slug' => 'photography',
        'city_slug' => 'gurugram',
        'city_name' => 'Gurugram',
        'headline' => 'Photography Services in Gurugram for Offices and Towers',
        'intro' => '<p>TheLastClicks provides photography services in Gurugram (Gurgaon) for developers, corporate teams, retailers and families, with crews travelling from our Noida studio. The city is a mix of glass towers, open-plan offices, malls and landscaped farmhouses. Each one fights a photographer differently: facades throw glare, overhead panels flatten interiors and security desks limit access. Planning is the difference between a flat record and a set of images people want to use.</p><p>We plan every shoot around the building, choosing lenses, light and timing to suit it. Below is what a professional photographer in Gurugram should cover for architecture, workplaces, retail and celebrations.</p>',
        'sections' => [
            [
                'heading' => 'Glass-facade towers and interiors for developers',
                'body' => '<p>Residential towers and commercial developments need images that show scale without distortion. We use tilt-shift lenses to keep verticals straight and balance window light against the interior, so views are not blown out.</p><ul><li>Show apartments and model units</li><li>Lobbies, amenities and podium landscaping</li><li>Exteriors planned around sun direction</li></ul><p>Sun direction decides how a tower reads, so we study each facade before choosing the sequence of shots. Styling matters as much as light: cushions, lamps and cables are adjusted on set, and clutter is removed before the shutter, not retouched out afterwards. Developers also need the same space shown at different times of day, so we note which frames depend on a particular light. See our work for <a href="/industries/real-estate">real estate photography and film</a>.</p>',
            ],
            [
                'heading' => 'Employer-brand photography for offices and careers pages',
                'body' => '<p>Workspaces around Cyber City and Golf Course Road are shared, bright and busy. Employer-brand images show people collaborating, not posing: open desks, meeting corners, break areas and the entrance.</p><p>The set is built for several uses at once, such as a careers page, an annual report and social posts, so we frame with print and web crops in mind. A mobile studio handles team headshots in the same visit, lit to match. Our <a href="/industries/corporate-shoots">corporate shoots</a> bring these pieces together.</p><p>Authenticity matters to candidates, so we ask staff to behave naturally and keep stand-in models to a minimum. Agree in advance which teams and desks may appear, and have a quiet room ready for the portrait corner.</p>',
            ],
            [
                'heading' => 'Startup and SaaS imagery near Udyog Vihar',
                'body' => '<p>Young companies often need founder portraits, team shots and lifestyle frames in a single session. We plan the day so a co-working corner, a stairwell or a whiteboard wall can become a believable backdrop.</p><p>Retouching holds skin natural and matches colour to your brand hex codes, so the images sit comfortably beside your product screens and pitch decks.</p><p>Small teams move fast, so we keep the kit light and the sessions short. Bring a rough list of where the images will appear, such as a launch page, a funding deck or a hiring post, and we will crop for each as we shoot.</p>',
            ],
            [
                'heading' => 'Mall launches and retail campaigns',
                'body' => '<p>Retail photography starts with a shot list agreed with your marketing team: storefront, shelf, staff and customer moments. Malls are managed spaces, so centre management must approve the shoot, and rules on lighting, tripods and footfall vary by property.</p><p>We raise those questions during planning, not on the day, which protects your launch schedule.</p><p>Retail frames also depend on timing. Shelves must be stocked and signage installed before we arrive, so confirm with the store team that displays are final. A pre-visit to check ceiling light and reflections from glass fronts prevents surprises.</p>',
            ],
            [
                'heading' => 'Farmhouse and banquet weddings',
                'body' => '<p>Large lawns and banquet halls suit pre-wedding and wedding coverage, with room for portraits, rituals and decor. We scout the venue for light and layout beforehand, noting where sunset falls, which corners stay shaded and where the main rituals will happen. A shared timeline with the family and planner helps us be in the right place for each moment. Details are on our page for <a href="/industries/wedding-pre-wedding">wedding and pre-wedding photography</a>.</p>',
            ],
            [
                'heading' => 'Working inside secured buildings: access, selects and cost',
                'body' => '<p>Towers have security desks, visitor passes and building-management rules. Send names, equipment lists and arrival points early, so gate staff are expecting the crew and lifts are booked. Mixed office lighting is common, so we bring portable lights to keep skin tones steady from one floor to the next.</p><p>Afterwards you receive curated selects, not a bulk dump, with retouching and export sizes prepared for each channel. Cost for photography services in Gurugram depends on shoot days, crew size, locations, image count and licensing scope. Share your brief through the <a href="/contact">contact page</a> for a scoped proposal.</p>',
            ],
        ],
        'highlights' => [
            'Crews travel to Gurugram from our Noida studio',
            'Offices, towers, retail spaces and weddings',
            'Tilt-shift lenses for straight vertical lines',
            'Mobile studio for team headshots',
            'Retouching matched to brand hex codes',
            'Curated selects, not folder dumps',
        ],
        'faqs' => [
            [
                'q' => 'What types of corporate photography work are most in demand in Gurgaon?',
                'a' => '<p>For photography services in Gurugram, team and leadership headshots, workplace interiors, employer-brand sets for careers pages and event coverage come up most often. Startups add founder and lifestyle imagery, while industrial units ask for plant documentation. Tell us where the images will run, and we will design the shot list around that use. A clear picture of the audience, such as candidates, investors or customers, changes which frames we prioritise.</p>',
            ],
            [
                'q' => 'How do you photograph glass buildings without glare?',
                'a' => '<p>We study where the sun falls on each facade and choose the angle and hour accordingly. Tilt-shift lenses keep verticals straight, and we avoid shooting straight at reflective panes. Inside, we balance window light against the room so views stay visible. Glare is managed in planning, not rescued in editing, and reflections from neighbouring towers are checked on site.</p>',
            ],
            [
                'q' => 'Do I need permission to shoot inside malls or office towers?',
                'a' => '<p>Yes. Malls require approval from centre management, and towers need clearance from building management and security. Share crew names and equipment lists in advance so passes and lift access are ready. Raise permissions during planning, since only the property can grant them.</p>',
            ],
            [
                'q' => 'What is employer-brand photography?',
                'a' => '<p>It is imagery that shows what working at a company feels like: real workspaces, teams collaborating and day-to-day moments, instead of stock images. The photos run on careers pages, recruitment posts and annual reports. A good set mixes candid scenes with consistent portraits so the story holds together. Teams usually approve the plan before the shoot, so nobody is surprised by what appears on the careers page.</p>',
            ],
        ],
        'meta_title' => 'Photography Services in Gurugram',
        'meta_description' => 'Photography services in Gurugram for glass towers, offices, retail launches and weddings, with crews from Noida. Send your brief for a scoped proposal.',
    ],
    [
        'service_slug' => 'post-production',
        'city_slug' => 'delhi',
        'city_name' => 'Delhi',
        'headline' => 'Video Post-Production Services in Delhi',
        'intro' => '<p>TheLastClicks provides video post-production services to Delhi agencies, brand teams and event companies: editing, colour grading and sound finishing on footage you have already shot. To be plain about it, all of that work happens at our studio in Sector 26, Noida. We do not run an edit suite in Delhi.</p><p>What Delhi gives us is the footage and the people who approve it. Material arrives from several cameras and several vendors, and a committee often signs it off. Our job is to turn that pile into one finished film, with a review process that works from a distance.</p>',
        'sections' => [
            [
                'heading' => 'Footage from Delhi, finished in Noida',
                'body' => '<p>Delhi is home to creative agencies, corporate communications departments, publishers and event companies. Many shoot with their own crews or hire freelancers, so footage reaches us from different cameras, operators and formats.</p><p>You do not need to have filmed with us. Our post-production team can ingest an existing archive or footage shot by another agency, colour-match it and cut it to our studio standard. The files travel to Noida, and the people who approve the film stay where they are.</p><p>In-house communications teams can see how this plays out on our <a href="/industries/corporate-shoots">corporate shoots page</a>.</p>',
            ],
            [
                'heading' => 'Editing conference, expo and venue recordings',
                'body' => '<p>Event footage is long, multi-camera and rarely tidy. Recordings from large venues such as Bharat Mandapam, Pragati Maidan, Yashobhoomi or Aerocity hotels usually carry several angles per session and audio from more than one source.</p><p>At ingest we back everything up to redundant servers, sync the angles and generate proxies so the edit stays responsive. The same material can then become several films:</p><ul><li>Keynote and session cuts that follow the programme</li><li>Highlight films for sponsors and attendees</li><li>Sizzle reels built from a past edition for the next pitch</li></ul><p>Audio is cleaned and balanced as part of the finish, so a speaker on a weak lapel mic still sounds consistent beside the one on the main desk. If coverage is still being planned, our <a href="/blog/corporate-event-video-coverage-guide">corporate event video coverage guide</a> shows what editors need from the shoot.</p>',
            ],
            [
                'heading' => 'Matching footage from several vendors into one look',
                'body' => '<p>When a photographer, a videographer and an in-house team all roll cameras at the same event, nothing lines up. Frame rates differ, exposure drifts and phones add their own colour.</p><p>We normalise frame rates and audio levels during ingest, before anyone starts cutting. Colour is then handled in an ACES-managed pipeline in DaVinci Resolve Studio, which gives mixed cameras a common starting point.</p><p>Brand LUTs are locked and version-controlled. A campaign cut in March and recut later still looks like the same campaign.</p><p>The same logic applies to titles and lower thirds. We set them once from your brand guidelines, so a sponsor logo or speaker name looks identical in the keynote cut and the highlight film.</p>',
            ],
            [
                'heading' => 'What you approve at each stage, from ingest to master',
                'body' => '<p>Every stage ends with something you can look at, so you always know what you are being asked to approve.</p><ul><li><strong>Ingest and sync:</strong> secure backup, synced angles, proxies</li><li><strong>Offline edit:</strong> a rough cut for structure and pacing</li><li><strong>Online and VFX:</strong> picture lock, titling, motion graphics, plate cleanups</li><li><strong>Colour and sound:</strong> graded picture with a finished mix</li><li><strong>Mastering:</strong> final files in 16:9, 9:16 and 4:5</li></ul><p>Plate cleanups happen before colour, so a stray object in an otherwise strong shot does not have to mean a reshoot. Our blog explains <a href="/blog/what-post-production-actually-includes">what post-production actually includes</a> in more depth.</p>',
            ],
            [
                'heading' => 'Reviewing a cut without the commute',
                'body' => '<p>Members of an organising committee rarely sit in the same room. We share frame-accurate, timecoded review links, so each comment lands on the exact frame it refers to.</p><p>Every project includes three structured rounds: rough cut, fine cut and final polish. Numbered versions mean the cut a sponsor approved is always the one you can find again.</p><p>Hard external dates, such as an event opening, are fixed at the brief so the rounds are planned around them.</p>',
            ],
            [
                'heading' => 'The software and monitoring behind the finish',
                'body' => '<p>The edit runs on Adobe Creative Cloud, mainly Premiere Pro, with After Effects for motion graphics and titles. Grading happens in DaVinci Resolve Studio on calibrated reference monitors.</p><p>Sound is finished in Pro Tools and Audition, with broadcast-compliant metering. Picture and sound are completed under one roof by the same team, and none of it is outsourced.</p><p>Calibrated monitors matter because a grade judged on an ordinary screen can look different on your client display. Reference monitors give everyone the same starting point.</p>',
            ],
            [
                'heading' => 'How to brief a post-only job',
                'body' => '<p>Tell us what was shot, who shot it, how many people approve and where the film will play. A list of delivery formats and any brand guidelines helps us plan the ingest. If the footage sits on camera cards, a drive or a cloud folder, say so, because it changes how we plan the transfer.</p><p>Cost depends on footage volume, number of cameras, graphics, sound work and the deliverables you need, so we quote after reading your brief. <a href="/contact">Send your brief through our contact page</a> and we will respond with next steps.</p>',
            ],
        ],
        'highlights' => [
            'Edit, grade and sound finished at our Sector 26, Noida studio',
            'Works on footage shot by your crew or another agency',
            'Timecoded review links with three feedback rounds',
            'DaVinci Resolve Studio and an ACES colour pipeline',
            'Masters in 16:9, 9:16 and 4:5',
        ],
        'faqs' => [
            [
                'q' => 'Can you edit footage that another agency or our in-house team shot?',
                'a' => '<p>Yes. We ingest the material, colour-match it and cut it to our studio standard, whether it is a full archive or a handful of camera cards. Mixed frame rates and uneven audio levels are normalised at ingest. At the brief, tell us which cameras were used, who shot what and whether any footage is already graded, so we can plan the colour match.</p>',
            ],
            [
                'q' => 'How do revision rounds work when approvers are in different offices?',
                'a' => '<p>Everyone reviews the same cut through one timecoded link, so notes sit on exact frames. Each project has three rounds: rough cut, fine cut and final polish. Versions are numbered, which keeps the approved cut easy to trace. Nobody needs to travel to Noida to sign off, and late joiners can open the same link and see every earlier comment.</p>',
            ],
            [
                'q' => 'What is the difference between colour correction and colour grading?',
                'a' => '<p>Correction fixes the technical side: exposure, white balance and matching shots so they sit together. Grading adds the creative look after that. We do both in an ACES-managed pipeline in DaVinci Resolve Studio, correction first, so footage from different cameras holds one look across the finished film.</p>',
            ],
            [
                'q' => 'How do we send large footage files, and how long do you keep them?',
                'a' => '<p>We agree the transfer method at the brief, based on file volume and your IT rules. Once received, footage is backed up to redundant servers. We keep active backups of raw footage and project files for a standard minimum window, so alternate cuts can be requested later. Ask us about the window when you brief.</p>',
            ],
        ],
        'meta_title' => 'Video Editing Services in Delhi',
        'meta_description' => 'Video post production services for Delhi agencies, brands and event teams. Editing, colour and sound are finished in Noida. Send us your footage today.',
    ],
    [
        'service_slug' => 'post-production',
        'city_slug' => 'noida',
        'city_name' => 'Noida',
        'headline' => 'Post-Production Studio in Noida',
        'intro' => '<p>TheLastClicks runs a post-production studio in Noida, at B-7, D-Block, Sector 26, where editing, colour grading and sound mixing all happen in our own rooms. We work with production houses, brands and agencies. Some want an editor beside the camera crew. Others want a finishing house for a film that was shot elsewhere.</p><p>If you are in Noida, the people cutting your film are a short conversation away, rather than a name on a ticket. Bring raw footage, a rough cut or a locked picture, and tell us which stage you need help with. We will say plainly whether the job suits a full edit or a single finishing stage.</p>',
        'sections' => [
            [
                'heading' => 'A Noida studio, not a remote service',
                'body' => '<p>Many editing services in the NCR are desks that pass work along. Ours is a physical studio with edit, grade and sound facilities, and the same team handles a project from ingest to master.</p><p>That matters when something needs explaining. You can talk to the editor about pacing and to the colourist about a skin tone, without a relay in between. Nothing is outsourced.</p><p>It also helps with small decisions that slow remote projects down: whether a cut feels slow, whether a logo sits too close to the edge, whether dialogue needs another pass. Those are answered by people who know the footage.</p><p>If your team is nearby, we will see what suits the project. We do not promise on-site edit sessions, but we are easy to reach.</p>',
            ],
            [
                'heading' => 'Grade-only and sound-only work on a locked cut',
                'body' => '<p>Not every job needs the full pipeline. Teams in the Film City cluster often hold an edit they are happy with and need one finishing stage done properly.</p><ul><li>Colour grading in DaVinci Resolve Studio for a picture-locked film</li><li>Sound design and mixing, finished to broadcast-compliant metering</li><li>Mastering to the delivery specification your client or channel has set</li></ul><p>At handover we confirm the formats and colour space of what you send, so the grade starts from the right place. Specification checks happen before the work begins, not after the first export.</p><p>Overflow work is common, too. A team with a full schedule can hand us a single reel, a batch of episodes or a finishing pass, and keep its own editors on the next project.</p>',
            ],
            [
                'heading' => 'Clean-ups for internal, training and webinar video',
                'body' => '<p>Company films made for staff or customers are rarely pretty at source. Rooms echo, slides change mid-sentence and the brand colours drift between recordings.</p><p>We repair audio, tighten pacing, add titles and graphics, and bring every recording into the same brand look. That consistency is what makes a library of internal films feel like one channel rather than a folder of unrelated clips. Recorded sessions, interviews and talk-format shows get similar treatment, and our page on <a href="/industries/podcast">podcast and video-talk production</a> explains how we approach spoken-word content.</p>',
            ],
            [
                'heading' => 'How a project moves through the studio',
                'body' => '<p>First comes data wrangling: footage is backed up to redundant servers, multi-camera angles are synced and proxies are built. An offline edit follows, cut for story and rhythm, and delivered as a rough cut for structural approval.</p><p>Online work comes next. Picture lock is reached, then motion graphics, titling and plate cleanups go in. Colour and sound follow, and mastering closes the job.</p><p>Each stage hands you something concrete to review, and nothing moves forward until you have seen it. If the footage was also shot by us, our <a href="/services/videography">videography service</a> keeps camera crew and editor on the same brief.</p>',
            ],
            [
                'heading' => 'Grade and sound finished in the same rooms',
                'body' => '<p>Picture and sound are completed at the same address. Colour is graded on calibrated reference monitors inside an ACES-managed pipeline, with brand LUTs locked and version-controlled.</p><p>Sound is mixed in Pro Tools and Audition: spot effects, ambience and music balance, checked against broadcast-compliant metering. The edit itself runs on Premiere Pro and After Effects.</p><p>Because nothing leaves the building, a note about the mix can be actioned alongside a note about the picture. Calibrated monitors keep the grade honest, and metering keeps loudness within broadcast limits before the master is made.</p>',
            ],
            [
                'heading' => 'Feedback rounds and what happens to your files',
                'body' => '<p>Review runs through frame-accurate, timecoded links. Three structured rounds are included: rough cut, fine cut and final polish.</p><p>After delivery, raw footage and project files stay on redundant backups for a standard minimum window. That is how a client can come back months later and ask for a shorter version, an updated title or a new logo. We suggest naming the files and versions clearly at handover, so everyone can find them again. Masters can be re-exported to a new platform specification without re-grading.</p>',
            ],
            [
                'heading' => 'What changes the cost of an edit',
                'body' => '<p>Cost is shaped by a few drivers: the volume of footage, how many cameras were used, how much graphics and VFX work is needed, the depth of sound work, and how many deliverables you need. A simple tidy-up of one interview sits at one end of that range, and a multi-camera event film with graphics and a full mix sits at the other.</p><p>Our guide to <a href="/blog/video-editing-cost-india">video editing cost in India</a> explains these factors in general terms. For a figure that fits your project, <a href="/contact">talk to us through the contact page</a>.</p>',
            ],
        ],
        'highlights' => [
            'Own edit, grade and sound rooms at B-7, D-Block, Sector 26',
            'Post-only: bring a locked cut for grade or mix',
            'Broadcast-compliant sound metering on every mix',
            'Premiere Pro, After Effects, Pro Tools and Audition',
            'Redundant backups, with alternate cuts possible later',
        ],
        'faqs' => [
            [
                'q' => 'Can you do only colour grading or only sound for a film we have already cut?',
                'a' => '<p>Yes. Send a picture-locked cut and we grade it, mix it or master it, whichever stage you need. We confirm formats and colour space at handover so the work starts from the right source. If the edit still needs changes, tell us early, since late picture changes affect both colour and sound and can mean redoing work.</p>',
            ],
            [
                'q' => 'Can one shoot become multiple deliverables?',
                'a' => '<p>Yes. Once the film is graded and mixed, we master it in 16:9, 9:16 and 4:5. Because the colour work is done once, a new platform specification can be exported later without re-grading. Tell us at the brief where each version will play so the framing is planned in the edit and nothing important is cropped out of the vertical version.</p>',
            ],
            [
                'q' => 'What do you need from us at handover, and what happens to our files afterwards?',
                'a' => '<p>We need the footage or locked cut, the delivery specification, brand guidelines and any reference films. Files go onto redundant backups, and we keep them for a standard minimum window, which is why alternate cuts remain possible months on. Ask us for the exact window when you brief.</p>',
            ],
            [
                'q' => 'How do feedback rounds work in the studio?',
                'a' => '<p>You receive a frame-accurate review link for each cut and comment directly on the frame. There are three rounds: rough cut for structure, fine cut for detail, and a final polish. That sequence stops a project drifting into open-ended revisions, because each round has a defined purpose and a clear end point.</p>',
            ],
        ],
        'meta_title' => 'Post Production Studio in Noida',
        'meta_description' => 'Post production studio in Noida with its own edit, colour and sound rooms. Bring raw footage or a locked cut and talk to the people finishing your film.',
    ],
    [
        'service_slug' => 'post-production',
        'city_slug' => 'gurugram',
        'city_name' => 'Gurugram',
        'headline' => 'Video Post-Production in Gurugram',
        'intro' => '<p>TheLastClicks provides post-production for Gurugram marketing teams, SaaS companies and real estate developers: editing, colour and sound for footage that has already been captured. Our editing, grading and sound rooms are in Noida, not in Gurugram, and you will not need to travel for any of it.</p><p>What we offer is a finishing process built for companies where several people have a say. Reviewers sit in different offices, brand rules matter, and sources range from drone to phone. We aim to hand you one film that everyone has signed off, without a trail of conflicting versions behind it.</p>',
        'sections' => [
            [
                'heading' => 'How Gurugram teams work with a Noida finishing studio',
                'body' => '<p>The hand-off is simple. You send footage, we ingest it and build the cut at our Noida studio, and your team reviews online. Whether you search for a video editing company in Gurgaon or a post-production house in Gurugram, the practical question is the same: how smoothly does a cut move between offices?</p><p>Our answer is a fixed sequence of stages, with a deliverable at each one, so nobody is guessing what comes next.</p><p>Footage is backed up to redundant servers on arrival, angles are synced, and an offline edit gives your team a rough cut to react to. Online work, colour, sound and mastering follow in order. Review happens by link, which suits teams spread across towers, campuses and cities.</p>',
            ],
            [
                'heading' => 'Sign-off across marketing, legal, HR and regional heads',
                'body' => '<p>A film for a Cyber City office may need approval from marketing, legal, HR and a regional head. Frame-accurate review links let each person comment on the exact frame, so nobody has to describe a moment by timecode in an email.</p><p>Three structured rounds, rough cut, fine cut and final polish, give the approval chain a sequence to follow.</p><p>We keep numbered versions of every cut. Legal and brand teams can then look at the same version at the same stage, and the one someone approved is always the one you can find again.</p><p>When two reviewers disagree, we do not pick a side. When two offices disagree, settle which note wins before the next round, so the edit moves forward instead of in circles.</p>',
            ],
            [
                'heading' => 'Product demos, calls and launch films for SaaS teams',
                'body' => '<p>Software companies tend to hold a mix of recorded customer calls, screen captures, founder talks and launch footage. Quality varies, and audio is usually the weakest part.</p><p>A recorded call may carry room echo or a thin laptop microphone. Cleaning that up lifts the whole film, because viewers forgive a soft image far sooner than muddy speech.</p><p>We ingest all of it, repair and balance the sound, add titles and motion graphics, and match the picture to your brand colours. A hero film can then be mastered in 16:9, with 9:16 and 4:5 versions for feeds and stories.</p><p>If the shoot is not done yet, our guide on <a href="/blog/how-to-brief-a-video-production-team">how to brief a video production team</a> helps you ask for the right material. For a clean handover, label each clip with its source and date.</p>',
            ],
            [
                'heading' => 'Drone, handheld and show-flat footage in one real estate film',
                'body' => '<p>Property films often combine aerial shots, gimbal walkthroughs and interior clips from different days. Skies change, white balance moves between rooms and the sound of a site is rarely usable.</p><p>We conform those sources during ingest and balance them in the grade, so a tower reads as one location in one light. Titles for floor plans, amenities and project names are added as motion graphics in the same brand style. Music and ambience are mixed to suit the story rather than simply added at the end.</p><p>Developers and brokers can read more about <a href="/industries/real-estate">our approach to real estate films</a>.</p>',
            ],
            [
                'heading' => 'Keeping one brand look across agencies and months',
                'body' => '<p>Large companies rarely use a single vendor, and each vendor brings its own camera settings and taste. A campaign may pass through two agencies and an internal team over a year.</p><p>Our colour pipeline follows ACES, and brand LUTs are locked and version-controlled, which means a film finished in one quarter matches the one finished in the next. When a new platform specification appears, we re-export the master without re-grading it.</p><p>Everything is finished in DaVinci Resolve Studio on calibrated monitors, with sound completed under the same roof. Because the same team handles the grade and the mix, you do not need to coordinate separate vendors for picture and audio.</p>',
            ],
            [
                'heading' => 'What to send for a post-only project',
                'body' => '<p>A useful package includes the footage, a list of who shot what, your brand guidelines, reference films and the names of everyone who must approve. Mention any internal rules about logos, disclaimers or legal text, since they affect the titles. Teams new to shoots can also read how to prepare, in our piece on <a href="/blog/preparing-your-team-for-a-corporate-shoot">preparing your team for a corporate shoot</a>.</p><p>Cost depends on footage volume, source variety, graphics, sound work and deliverables. We would rather quote on a real brief, so <a href="/contact">contact us with your project details</a>.</p>',
            ],
        ],
        'highlights' => [
            'All editing, grading and sound done at our Noida studio',
            'One timecoded review link per cut, three rounds',
            'Brand LUTs locked and version-controlled',
            'Drone, handheld and phone footage conformed to one look',
            'Masters re-exported to new specs without re-grading',
        ],
        'faqs' => [
            [
                'q' => 'How do several people across our offices review one cut?',
                'a' => '<p>Everyone gets the same timecoded review link and comments on the frame they mean. Send each round as one consolidated list rather than notes scattered across email and chat, so every change traces back to a single request. Where two comments contradict, we flag them so your team can decide before the next of the three rounds begins. That keeps a large approval chain moving in one direction.</p>',
            ],
            [
                'q' => 'Can you match our existing brand look?',
                'a' => '<p>Yes. Share earlier films or brand guidelines and we build the grade inside an ACES-managed pipeline in DaVinci Resolve Studio. Brand LUTs are then locked and version-controlled, so later films match earlier ones, even when a different team commissions them. We can also cut footage originally shot by another agency to that same look.</p>',
            ],
            [
                'q' => 'Can you combine drone, handheld and phone footage into one film?',
                'a' => '<p>Yes. Differences in frame rate, exposure and audio level are dealt with at ingest, and colour is matched shot by shot in the grade. Phone clips usually need the most care. Tell us the source of each clip and we plan the conform before the edit begins, which saves rework once the grade starts.</p>',
            ],
            [
                'q' => 'What file formats and sizes do you deliver?',
                'a' => '<p>We master in 16:9, 9:16 and 4:5, which covers web, broadcast and internal archive use. Because the grade is finished once, a different specification can be exported later without re-grading. Share your channel requirements at the brief and we confirm the delivery list before work starts, so there are no surprises when files are handed over.</p>',
            ],
        ],
        'meta_title' => 'Video Editing Services in Gurugram',
        'meta_description' => 'Video post production in Gurugram for marketing, SaaS and real estate teams. Editing and sound are done in Noida, with online review. Share your footage.',
    ],
];
