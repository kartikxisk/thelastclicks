<?php

namespace App\Models;

use App\Models\Concerns\HasMediaItems;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Work extends Model implements HasMedia
{
    use HasMediaItems, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'title', 'slug', 'summary', 'client', 'year', 'order', 'is_published', 'is_featured',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            // Respect a slug that was set deliberately (seeder or admin form).
            // Without this, a generated slug overwrites it, seeders keyed on slug
            // never match their own rows, and every run creates duplicates.
            ->skipGenerateWhen(fn () => filled($this->slug));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    /** @param Builder<Work> $q
     * @return Builder<Work>
     */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }
}
