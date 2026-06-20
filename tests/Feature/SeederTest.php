<?php

use App\Models\Crew;
use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('seeds roles, admin user, services, industries, crew, sample content', function () {
    $this->seed();

    expect(Role::pluck('name')->all())->toContain('Super-admin', 'Editor', 'Sales', 'Viewer')
        ->and(User::where('email', config('app.admin_seed_email'))->exists())->toBeTrue()
        ->and(Service::count())->toBe(7)
        ->and(Industry::count())->toBeGreaterThanOrEqual(4)
        ->and(Crew::count())->toBeGreaterThanOrEqual(3)
        ->and(Portfolio::published()->count())->toBeGreaterThanOrEqual(3)
        ->and(Post::published()->count())->toBeGreaterThanOrEqual(3)
        ->and(SiteSetting::get('contact_email'))->toBe('hello@thelastclicks.com');
});
