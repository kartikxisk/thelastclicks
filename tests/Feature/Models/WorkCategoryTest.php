<?php

use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\WorkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates a slug from the title', function () {
    $cat = WorkCategory::factory()->create(['title' => 'Fashion Show']);
    expect($cat->slug)->toBe('fashion-show');
});

it('belongs to an industry and lists ordered categories', function () {
    $industry = Industry::factory()->create();
    WorkCategory::factory()->create(['industry_id' => $industry->id, 'title' => 'B cat', 'order' => 2]);
    WorkCategory::factory()->create(['industry_id' => $industry->id, 'title' => 'A cat', 'order' => 1]);

    expect($industry->workCategories()->pluck('title')->all())->toBe(['A cat', 'B cat'])
        ->and(WorkCategory::first()->industry)->toBeInstanceOf(Industry::class);
});

it('portfolio belongs to a work category, nulled on category delete', function () {
    $cat = WorkCategory::factory()->create();
    $p = Portfolio::factory()->create(['work_category_id' => $cat->id, 'industry_id' => $cat->industry_id]);

    expect($p->workCategory->id)->toBe($cat->id)
        ->and($cat->portfolios()->count())->toBe(1);

    $cat->delete();
    expect($p->fresh()->work_category_id)->toBeNull();
});
