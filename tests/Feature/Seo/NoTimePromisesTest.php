<?php

use App\Models\Industry;
use App\Models\Post;
use App\Models\SeoPage;
use App\Models\Service;
use App\Models\ServiceLocation;
use Database\Seeders\PageSeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    // Outside db:seed under testing (see DatabaseSeeder), but it writes the live
    // contact and portfolio descriptions on every deploy, so it is checked too.
    $this->seed(PageSeoSeeder::class);
});

/**
 * Time depends on the project and the client exactly as price does, so the
 * public copy names no turnaround, delivery or reply window. "A highlight cut in
 * days" shipped in a seeded meta description and an industry body and sat in
 * production until an SEO audit read it back as a promise the studio cannot
 * control its own side of.
 */
it('promises no turnaround in any seeded public copy', function () {
    $pattern = '/\b(within\s+(a\s+few\s+)?(\d+\s+)?(hours?|days?|weeks?)|in\s+(a\s+few\s+)?days|same[- ]day|overnight|\d+\s*(hours?|hrs|days?)\s+(turnaround|delivery))\b/i';

    $copy = collect()
        ->merge(Industry::all()->map(fn (Industry $i) => [$i->slug, $i->summary.' '.$i->body]))
        ->merge(Service::all()->map(fn (Service $s) => [$s->slug, json_encode($s->getAttributes(), JSON_UNESCAPED_UNICODE)]))
        ->merge(ServiceLocation::all()->map(fn (ServiceLocation $l) => [$l->service_id.'/'.$l->city_slug, $l->headline.' '.$l->intro.' '.json_encode($l->sections, JSON_UNESCAPED_UNICODE).' '.$l->meta_title.' '.$l->meta_description]))
        ->merge(Post::all()->map(fn (Post $p) => [$p->slug, $p->excerpt.' '.$p->body.' '.$p->seo_description]))
        ->merge(SeoPage::all()->map(fn (SeoPage $p) => [$p->page_url, $p->title.' '.$p->meta_description.' '.$p->og_description]));

    $offenders = $copy
        ->filter(fn (array $row) => preg_match($pattern, strip_tags((string) $row[1])))
        ->map(fn (array $row) => $row[0])
        ->values()
        ->all();

    expect($offenders)->toBe([]);
});
