<?php

namespace App\Models;

use App\Models\Concerns\HasMediaItems;
use App\Models\Concerns\TouchesFrontend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Work extends Model implements HasMedia
{
    use HasMediaItems, HasSlug, InteractsWithMedia, TouchesFrontend;

    protected $fillable = [
        'title', 'slug', 'summary', 'client', 'category', 'crafts', 'credits',
        'location', 'agency', 'preview_video_url', 'year', 'order', 'is_published', 'is_featured',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'crafts' => 'array',
        'credits' => 'array',
    ];

    /**
     * Disciplines a project can be filed under — the grid's primary filter axis.
     * Keyed by slug because the front end filters on the slug, not the label.
     *
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'brand-film' => 'Brand Film',
        'commercial' => 'Commercial',
        'corporate' => 'Corporate',
        'wedding' => 'Wedding',
        'product' => 'Product',
        'event' => 'Event',
        'editorial' => 'Editorial',
        'music-video' => 'Music Video',
    ];

    /**
     * In-house crafts. These exist to make the post-production USP provable per
     * project rather than claimed once on a services page — the same reason The
     * Mill filters its work by Colour and VFX.
     *
     * @var array<string, string>
     */
    public const CRAFTS = [
        'edit' => 'Edit',
        'colour' => 'Colour',
        'sound' => 'Sound',
        'vfx' => 'VFX',
        'motion' => 'Motion',
        'retouch' => 'Retouch',
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

    /** Human label for the filed category, or null when unfiled. */
    public function categoryLabel(): ?string
    {
        return self::CATEGORIES[$this->category] ?? null;
    }

    /**
     * Craft slugs, filtered to ones we actually know — a stale slug left behind
     * by an edited CRAFTS list would otherwise render as a blank chip.
     *
     * @return list<string>
     */
    public function craftSlugs(): array
    {
        return array_values(array_filter(
            $this->crafts ?? [],
            fn ($slug) => isset(self::CRAFTS[$slug])
        ));
    }

    /** @return list<string> */
    public function craftLabels(): array
    {
        return array_map(fn ($slug) => self::CRAFTS[$slug], $this->craftSlugs());
    }

    /**
     * Named credits, dropping rows that are missing either half — a half-filled
     * repeater row is a saving accident, not a credit.
     *
     * @return list<array{role: string, name: string}>
     */
    public function creditRows(): array
    {
        return array_values(array_filter(
            array_map(
                fn ($row) => ['role' => trim((string) ($row['role'] ?? '')), 'name' => trim((string) ($row['name'] ?? ''))],
                $this->credits ?? []
            ),
            fn ($row) => $row['role'] !== '' && $row['name'] !== ''
        ));
    }

    /**
     * Muted loop for the grid tile: the explicit short preview if one is set,
     * else the first uploaded video row. YouTube is deliberately excluded —
     * an iframe per tile is far too heavy for a grid, and can't be muted-looped
     * reliably across browsers.
     */
    public function previewVideoUrl(): ?string
    {
        if (filled($this->preview_video_url)) {
            return $this->preview_video_url;
        }

        foreach ($this->mediaItems as $item) {
            if ($item->type === 'video' && ($url = $item->resolvedUrl())) {
                return $url;
            }
        }

        return null;
    }

    /**
     * The homepage collage embeds works, so a rename has to drop it too.
     *
     * @return list<string>
     */
    public function frontendCacheTags(): array
    {
        return ['works', 'works:'.$this->slug, 'pages:home'];
    }
}
