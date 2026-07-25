<?php

namespace App\Models;

use App\Support\MediaUrl;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'pillars', 'phases', 'kit', 'faqs', 'cta', 'tags', 'gallery_urls',
        'hero_url', 'featured_slug', 'body', 'order', 'share',
    ];

    protected $casts = [
        'hero_meta' => 'array',
        'proof' => 'array',
        'pillars' => 'array',
        'phases' => 'array',
        'kit' => 'array',
        'faqs' => 'array',
        'cta' => 'array',
        'tags' => 'array',
        'gallery_urls' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
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
