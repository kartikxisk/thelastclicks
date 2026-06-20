<?php

namespace App\Models;

use Database\Factories\CrewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Crew extends Model implements HasMedia
{
    /** @use HasFactory<CrewFactory> */
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $table = 'crew';

    protected $fillable = [
        'slug', 'name', 'role', 'tagline', 'joined', 'discipline', 'city',
        'bio', 'skills', 'credits', 'photo_url', 'social_json', 'order',
    ];

    protected $casts = [
        'social_json' => 'array',
        'skills' => 'array',
        'credits' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('headshot')->singleFile();
    }
}
