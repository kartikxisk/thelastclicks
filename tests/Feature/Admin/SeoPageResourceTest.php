<?php

use App\Filament\Resources\SeoPageResource\Pages\CreateSeoPage;
use App\Filament\Resources\SeoPageResource\Pages\EditSeoPage;
use App\Filament\Resources\SeoPageResource\Pages\ListSeoPages;
use App\Models\SeoPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::where('email', config('app.admin_seed_email'))->firstOrFail());
});

it('Super-admin can list seo pages', function () {
    SeoPage::create(['page_url' => '/about', 'title' => 'About SEO']);

    // Page size raised past the seeded set. The table paginates at ten, and the
    // seeder now writes ten rows on its own — three services, six industries and
    // the industries deck — so "can see every record" silently became "can see
    // the first page of them" and failed on the row this test creates.
    Livewire::test(ListSeoPages::class)
        ->set('tableRecordsPerPage', 50)
        ->assertCanSeeTableRecords(SeoPage::all());
});

it('Super-admin can create a seo page', function () {
    Livewire::test(CreateSeoPage::class)
        ->fillForm([
            'page_url' => '/contact',
            'label' => 'Contact page',
            'title' => 'Contact Us — TheLastClicks',
            'meta_description' => 'Talk to the studio.',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(SeoPage::where('page_url', '/contact')->exists())->toBeTrue();
});

it('Super-admin can edit a seo page', function () {
    $row = SeoPage::create(['page_url' => '/about', 'title' => 'Old Title']);

    Livewire::test(EditSeoPage::class, ['record' => $row->id])
        ->fillForm(['title' => 'New Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($row->fresh()->title)->toBe('New Title');
});

it('rejects a duplicate page url', function () {
    SeoPage::create(['page_url' => '/about']);

    Livewire::test(CreateSeoPage::class)
        ->fillForm(['page_url' => '/about'])
        ->call('create')
        ->assertHasFormErrors(['page_url']);
});

it('normalizes a page url entered without a leading slash', function () {
    Livewire::test(CreateSeoPage::class)
        ->fillForm(['page_url' => 'about'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(SeoPage::where('page_url', '/about')->exists())->toBeTrue();
});
