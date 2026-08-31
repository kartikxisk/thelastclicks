<?php

namespace App\Models;

use App\Support\MediaUrl;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Service extends Model implements HasMedia
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'slug', 'title', 'hero_copy', 'hero_headline', 'hero_meta', 'proof',
        'pillars', 'phases', 'kit', 'faqs', 'sections', 'cta', 'tags', 'gallery_urls',
        'hero_url', 'featured_slug', 'body', 'order', 'share',
    ];

    protected $casts = [
        'hero_meta' => 'array',
        'proof' => 'array',
        'pillars' => 'array',
        'phases' => 'array',
        'kit' => 'array',
        'faqs' => 'array',
        'sections' => 'array',
        'cta' => 'array',
        'tags' => 'array',
        'gallery_urls' => 'array',
    ];

    /**
     * Every project linked to this service, published or not.
     *
     * Deliberately unfiltered, and the admin multi-select binds to this one.
     * Filament's Select::relationship() runs the relation query to load the
     * field's current state as well as its options, so a filtered relation here
     * would hide an attached-but-unpublished project from the form — and saving
     * the form would then write back the reduced set, silently detaching it. The
     * public page reads publishedWorks() instead.
     *
     * @return BelongsToMany<Work, $this>
     */
    public function works(): BelongsToMany
    {
        return $this->belongsToMany(Work::class);
    }

    /**
     * Industries this service page says it covers.
     *
     * An editorial claim rather than something derived from the attached work —
     * a service with nothing filed under it yet still covers its verticals.
     * Ordered by the industry's own order so the block matches the deck.
     *
     * @return BelongsToMany<Industry, $this>
     */
    public function industries(): BelongsToMany
    {
        return $this->belongsToMany(Industry::class)
            ->orderBy('industries.order')
            ->orderBy('industries.id');
    }

    /**
     * Projects shown on this service's page.
     *
     * Published-only and ordered by the work's own `order`, so the sequence
     * matches the portfolio and a draft cannot reach a live page by being
     * attached here.
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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            // Without this, renaming a service silently rewrites its URL and
            // every inbound link 404s. A slug that already exists is a published
            // address — changing it has to be a deliberate act, not a side
            // effect of editing the display title. Matches Work::getSlugOptions.
            ->skipGenerateWhen(fn () => filled($this->slug));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')->singleFile();
        $this->addMediaCollection('gallery');
    }

    /**
     * Hero image URL: an admin-attached upload wins, else the seeded hero_url —
     * which holds an S3 key resolved against the media disk, so the CloudFront
     * host is read from config at runtime. Absolute URLs pass through untouched.
     */
    public function heroUrl(): ?string
    {
        return $this->getFirstMediaUrl('hero') ?: MediaUrl::onMediaDisk($this->hero_url);
    }

    /**
     * Gallery URLs: admin-attached media wins, else each seeded gallery entry
     * resolved against the media disk (S3 key or absolute URL).
     *
     * @return list<string>
     */
    public function galleryUrls(): array
    {
        $media = $this->getMedia('gallery');

        if ($media->isNotEmpty()) {
            return $media->map(fn ($m) => $m->getUrl())->all();
        }

        return collect($this->gallery_urls ?? [])
            ->map(fn ($u) => MediaUrl::onMediaDisk($u))
            ->filter()
            ->values()
            ->all();
    }
}
