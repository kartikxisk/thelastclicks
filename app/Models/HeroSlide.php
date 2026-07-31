<?php

namespace App\Models;

use App\Models\Concerns\TouchesFrontend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * One homepage hero background — a still or a film, uploaded in the admin.
 *
 * With no active slides the hero falls back to the bundled CDN reel, so a fresh
 * install still renders. One slide behaves exactly like the old single-reel hero;
 * two or more cross-fade.
 */
class HeroSlide extends Model implements HasMedia
{
    use InteractsWithMedia, TouchesFrontend;

    protected $fillable = ['label', 'order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('asset')->singleFile();
        // Painted while a video slide buffers, and the LCP element on slide one.
        $this->addMediaCollection('poster')->singleFile();
    }

    /** @param Builder<HeroSlide> $q
     * @return Builder<HeroSlide>
     */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('order')->orderBy('id');
    }

    protected function asset(): ?Media
    {
        return $this->getFirstMedia('asset');
    }

    public function assetUrl(): ?string
    {
        return $this->asset()?->getUrl();
    }

    public function isVideo(): bool
    {
        return str_starts_with((string) $this->asset()?->mime_type, 'video/');
    }

    /**
     * Video poster. Falls back to nothing rather than to the asset itself — a
     * <video poster> pointing at an mp4 renders as a broken frame.
     */
    public function posterUrl(): ?string
    {
        return $this->getFirstMediaUrl('poster') ?: null;
    }

    /** The frame the browser should paint first: poster for film, the still itself otherwise. */
    public function previewUrl(): ?string
    {
        return $this->isVideo() ? $this->posterUrl() : $this->assetUrl();
    }

    /**
     * Hero slides appear on the homepage only.
     *
     * @return list<string>
     */
    public function frontendCacheTags(): array
    {
        return ['pages:home'];
    }
}
