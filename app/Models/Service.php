<?php

namespace App\Models;

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
}
