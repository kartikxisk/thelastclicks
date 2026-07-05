<?php

namespace App\Models;

use Database\Factories\PortfolioFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Portfolio extends Model implements HasMedia
{
    /** @use HasFactory<PortfolioFactory> */
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'owner_id', 'service_id', 'industry_id', 'work_category_id', 'title', 'slug', 'client',
        'location', 'year', 'body', 'approach', 'credits', 'cover_url',
        'gallery_urls', 'hero_html', 'status',
    ];

    protected $casts = [
        'credits' => 'array',
        'gallery_urls' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    /** @param Builder<Portfolio> $q
     * @return Builder<Portfolio>
     */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsTo<Industry, $this> */
    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    /** @return BelongsTo<WorkCategory, $this> */
    public function workCategory(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
