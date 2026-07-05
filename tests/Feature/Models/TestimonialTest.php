<?php

use App\Models\Industry;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('published scope excludes unpublished rows', function () {
    Testimonial::factory()->create(['is_published' => false, 'client_name' => 'Hidden']);
    Testimonial::factory()->create(['is_published' => true, 'client_name' => 'Shown']);

    expect(Testimonial::published()->pluck('client_name')->all())->toBe(['Shown']);
});

it('optionally belongs to an industry, nulled on industry delete', function () {
    $industry = Industry::factory()->create();
    $t = Testimonial::factory()->create(['industry_id' => $industry->id]);

    expect($industry->testimonials()->count())->toBe(1);

    $industry->delete();
    expect($t->fresh()->industry_id)->toBeNull();
});
