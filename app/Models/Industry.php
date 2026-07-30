<?php

namespace App\Models;

use App\Models\Concerns\HasMediaItems;
use App\Models\Concerns\TouchesFrontend;
use App\Support\MediaUrl;
use Database\Factories\IndustryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Industry extends Model implements HasMedia
{
    /** @use HasFactory<IndustryFactory> */
    use HasFactory, HasMediaItems, HasSlug, InteractsWithMedia, TouchesFrontend;

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

    /**
     * The homepage deck embeds industries.
     *
     * @return list<string>
     */
    public function frontendCacheTags(): array
    {
        return ['industries', 'pages:home'];
    }
}
