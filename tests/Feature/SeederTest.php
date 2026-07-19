<?php

use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('seeds roles, admin user, services, industries, sample content', function () {
    $this->seed();

    expect(Role::pluck('name')->all())->toContain('Super-admin', 'Editor', 'Sales', 'Viewer')
        ->and(User::where('email', config('app.admin_seed_email'))->exists())->toBeTrue()
        ->and(Service::count())->toBe(3)
        ->and(Industry::count())->toBe(7)
        ->and(Industry::pluck('slug'))->toContain('weddings-celebrations', 'motion-post-production')
        ->and(Testimonial::published()->count())->toBeGreaterThanOrEqual(4)
        ->and(Portfolio::published()->count())->toBeGreaterThanOrEqual(3)
        ->and(Post::published()->count())->toBeGreaterThanOrEqual(3)
        ->and(SiteSetting::get('contact_email'))->toBe('hello@thelastclicks.com');
});

it('seeds homepage strip and hero video settings', function () {
    $this->seed();

    $strip = SiteSetting::get('home_strip');
    expect($strip)->toHaveCount(6)
        ->and($strip[0]['portfolio_slug'])->toBe('ins-navy')
        ->and($strip[0])->toHaveKeys(['portfolio_slug', 'tag', 'title', 'meta']);

    expect(SiteSetting::get('hero_videos'))
        ->toBe(['ins-navy', 'salesforce-blr', 'rahul-dravid-teaser']);
});

it('portfolio seeder no longer writes legacy /videos paths', function () {
    $this->seed();

    $p = Portfolio::where('slug', 'ins-navy')->firstOrFail();
    expect($p->cover_url)->toBeNull()
        ->and($p->gallery_urls)->toBeNull();
});
