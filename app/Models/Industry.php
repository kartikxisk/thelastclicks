<?php

namespace App\Models;

use App\Models\Concerns\HasMediaItems;
use App\Support\MediaUrl;
use Database\Factories\IndustryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Industry extends Model implements HasMedia
{
    /** @use HasFactory<IndustryFactory> */
    use HasFactory, HasMediaItems, HasSlug, InteractsWithMedia;

    protected $fillable = ['slug', 'title', 'summary', 'image_url', 'body', 'order'];

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
        $this->addMediaCollection('hero')->singleFile();
    }

    /** @return HasMany<Testimonial, $this> */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class)->orderBy('order');
    }

    /**
     * Projects filed under this industry.
     *
     * The same pivot Work::industries() reads, from the other end — the admin
     * edits the link from whichever record is already open. Unfiltered on
     * purpose: Filament's Select::relationship() runs this query to load the
     * field's current state as well as its options, so filtering here would hide
     * an attached-but-unpublished project from the form, and saving would then
     * write back the reduced set and silently detach it. The portfolio applies
     * its own published scope.
     *
     * @return BelongsToMany<Work, $this>
     */
    public function works(): BelongsToMany
    {
        return $this->belongsToMany(Work::class);
    }

    /**
     * Services that say they cover this industry.
     *
     * Read from the service page, and edited from either end in the admin.
     *
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->orderBy('services.order')
            ->orderBy('services.id');
    }

    /**
     * Projects shown on this industry's page.
     *
     * Published-only and ordered by the work's own `order`, so the sequence
     * matches the portfolio and a draft cannot reach a live page by being
     * attached here. Mirrors Service::publishedWorks().
     *
     * @return BelongsToMany<Work, $this>
     */
    public function publishedWorks(): BelongsToMany
    {
        return $this->works()
            ->where('is_published', true)
            ->orderBy('works.order')
            ->orderBy('works.id');
    }

    protected function mediaCoverCollection(): string
    {
        return 'hero';
    }

    protected function mediaCoverFallback(): ?string
    {
        return MediaUrl::onMediaDisk($this->image_url);
    }

    /**
     * Industries lead with a curated editorial still, so the uploaded hero (then
     * the image_url) wins over any gallery media — otherwise a YouTube poster in
     * the media array would hijack the card cover. Falls through to the media
     * array only when neither is set.
     */
    public function coverUrl(): ?string
    {
        return $this->getFirstMediaUrl('hero')
            ?: (MediaUrl::onMediaDisk($this->image_url) ?? $this->firstMediaItemCover());
    }
}
