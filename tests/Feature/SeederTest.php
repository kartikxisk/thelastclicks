<?php

use App\Models\Industry;
use App\Models\MediaItem;
use App\Models\Post;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\IndustriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('seeds roles, admin user, services, industries, sample content', function () {
    $this->seed();

    expect(Role::pluck('name')->all())->toContain('Super-admin', 'Editor', 'Sales', 'Viewer')
        ->and(User::where('email', config('app.admin_seed_email'))->exists())->toBeTrue()
        ->and(Service::count())->toBe(3)
        ->and(Industry::count())->toBe(8)
        ->and(Industry::pluck('slug'))->toContain('weddings-celebrations', 'corporate-enterprise', 'automobile-luxury', 'lifestyle-beverage')
        ->and(Testimonial::published()->count())->toBeGreaterThanOrEqual(4)
        ->and(Post::published()->count())->toBeGreaterThanOrEqual(3)
        ->and(SiteSetting::get('contact_email'))->toBe('info@thelastclicks.com');
});

it('does not leak media when the seeder retires an admin-created industry outside the hardcoded 7', function () {
    config(['media-library.disk_name' => 's3']);
    Storage::fake('s3');

    $this->seed();

    // An industry the seeder retires by name, carrying an uploaded media row.
    $retired = Industry::create([
        'slug' => 'motion-graphics',
        'title' => 'Motion Graphics',
        'summary' => 'Retired in a later seed version.',
        'image_url' => null,
        'body' => '',
        'order' => 99,
    ]);
    $item = $retired->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $item->addMedia(UploadedFile::fake()->image('leak.jpg'))->toMediaCollection('file');

    // An industry a panel user added — must survive every deploy.
    $adminAdded = Industry::create([
        'slug' => 'admin-created-vertical',
        'title' => 'Admin Created Vertical',
        'summary' => 'A sector added via the admin panel.',
        'image_url' => null,
        'body' => '',
        'order' => 98,
    ]);
    $kept = $adminAdded->mediaItems()->create(['type' => 'image', 'order' => 1]);
    $kept->addMedia(UploadedFile::fake()->image('keep.jpg'))->toMediaCollection('file');

    expect(MediaItem::count())->toBe(2)
        ->and(Media::count())->toBe(2);

    // Mirrors `php artisan db:seed --force` running on every production deploy.
    $this->seed(IndustriesSeeder::class);

    // Retired slug is gone *with* its media (no orphaned rows, no leaked files)…
    expect(Industry::where('slug', 'motion-graphics')->exists())->toBeFalse()
        // …while the admin-created industry and its media are untouched.
        ->and(Industry::where('slug', 'admin-created-vertical')->exists())->toBeTrue()
        ->and(MediaItem::count())->toBe(1)
        ->and(Media::count())->toBe(1);
});
