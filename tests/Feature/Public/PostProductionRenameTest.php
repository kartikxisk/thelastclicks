<?php

use App\Models\SeoPage;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/**
 * The editing service is addressed as /services/post-production.
 *
 * It was displayed as "Post Production" while living at /services/editing, so
 * the nav said one thing and the page said another. The slug now matches the
 * name, which means the old address has to keep resolving — it is published and
 * indexed — and the redirect that used to point the other way has to reverse.
 */
it('serves the service at its new slug', function () {
    $this->get('/services/post-production')
        ->assertOk()
        ->assertSee('Post Production');
});

it('301s the old editing url to the new one', function () {
    $this->get('/services/editing')
        ->assertStatus(301)
        ->assertRedirect('/services/post-production');
});

it('keeps the other retired service urls pointing at the renamed page', function () {
    foreach (['/services/social-content', '/services/creative-direction'] as $retired) {
        $this->get($retired)
            ->assertStatus(301)
            ->assertRedirect('/services/post-production');
    }
});

it('does not delete the renamed service when the seeder runs again', function () {
    // The retire list used to contain 'post-production', from when the service
    // moved the other way. Left there, the next deploy would delete the service
    // and cascade its media off S3.
    $this->seed();

    expect(Service::where('slug', 'post-production')->count())->toBe(1)
        ->and(Service::where('slug', 'editing')->exists())->toBeFalse()
        ->and(Service::count())->toBe(3);
});

it('renames rather than duplicating when a database already holds the old slug', function () {
    // The migration path: an environment that has been live carries slug
    // `editing`, and the seeder is keyed on slug, so without the rename it would
    // create a second row and leave the original stranded behind a redirect.
    Service::where('slug', 'post-production')->update(['slug' => 'editing']);

    $this->artisan('db:seed', ['--class' => 'Database\Seeders\ServicesSeeder', '--force' => true]);

    expect(Service::where('slug', 'editing')->exists())->toBeFalse()
        ->and(Service::where('slug', 'post-production')->count())->toBe(1);
});

it('moves the seo override onto the new url', function () {
    // SeoPage::forPath() matches on the exact URL, so a row left on the old path
    // silently stops applying and the page falls back to its Blade defaults.
    expect(SeoPage::where('page_url', '/services/post-production')->where('is_active', true)->exists())->toBeTrue()
        ->and(SeoPage::where('page_url', '/services/editing')->where('is_active', true)->exists())->toBeFalse();
});

it('links the new url from the footer', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="'.url('/services/post-production').'"', false)
        ->assertDontSee('href="'.url('/services/editing').'"', false);
});

it('lists the renamed service in the sitemap', function () {
    // --force: the command refuses to write against a non-public APP_URL, which
    // is exactly what the test environment has.
    $this->artisan('sitemap:generate', ['--force' => true]);

    expect(file_get_contents(public_path('sitemap.xml')))
        ->toContain('<loc>'.url('/services/post-production').'</loc>')
        ->not->toContain('<loc>'.url('/services/editing').'</loc>');
});
