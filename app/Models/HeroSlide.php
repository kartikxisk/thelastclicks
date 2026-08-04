<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * One homepage hero background — a still or a film, uploaded in the admin.
 *
 * With no active slides the hero renders no background at all — there is no
 * bundled fallback reel, so an empty admin shows an empty hero rather than
 * footage the editor cannot locate or replace. One slide behaves exactly like
 * the old single-reel hero; two or more cross-fade.
 */
class HeroSlide extends Model implements HasMedia
{
    use InteractsWithMedia;

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

    /** Extensions treated as film when the mime type is inconclusive. */
    private const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'm4v'];

    public function isVideo(): bool
    {
        $asset = $this->asset();

        if ($asset === null) {
            return false;
        }

        if (str_starts_with((string) $asset->mime_type, 'video/')) {
            return true;
        }

        // Mime sniffing is not always conclusive: an upload that arrives without
        // a Content-Type, or through a proxy that flattens it, is stored as
        // application/octet-stream. Trusting the mime alone would then render an
        // <img> pointing at an mp4 — the same broken frame posterUrl() guards
        // against. The extension is the tiebreaker.
        $extension = strtolower(pathinfo((string) $asset->file_name, PATHINFO_EXTENSION));

        return in_array($extension, self::VIDEO_EXTENSIONS, true);
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
}
