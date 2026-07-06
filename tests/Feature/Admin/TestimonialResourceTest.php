<?php

use App\Filament\Resources\TestimonialResource\Pages\EditTestimonial;
use App\Filament\Resources\TestimonialResource\Pages\ListTestimonials;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list testimonials', function () {
    Livewire::test(ListTestimonials::class)->assertCanSeeTableRecords(Testimonial::all());
});

it('Super-admin can edit a testimonial client name', function () {
    $t = Testimonial::first();
    Livewire::test(EditTestimonial::class, ['record' => $t->getRouteKey()])
        ->fillForm(['client_name' => 'New Client'])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($t->fresh()->client_name)->toBe('New Client');
});

it('Editor can update testimonials', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');
    expect($editor->can('update', Testimonial::first()))->toBeTrue();
});
