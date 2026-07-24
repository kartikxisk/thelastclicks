<?php

namespace App\Models\Concerns;

use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Shared media-array behaviour: an ordered list of mixed image / video /
 * YouTube rows, plus the cover and lightbox payload derived from them.
 */
trait HasMediaItems
{
    /**
     * Delete child media rows through Eloquent so medialibrary's own cleanup
     * runs — a DB-level or Builder-level delete would drop the rows without
     * firing events, orphaning the media records and their files on S3.
     * Structural: any model adopting this trait gets the cascade for free and
     * can't forget to wire it up in its own observer.
     */
    public static function bootHasMediaItems(): void
    {
        static::deleting(function ($model): void {
            $model->mediaItems()->cursor()->each->delete();
        });
    }

    /** @return MorphMany<MediaItem, $this> */
    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable')->orderBy('order')->orderBy('id');
    }

    /** Collection holding this model's explicit cover image. */
    protected function mediaCoverCollection(): string
    {
        return 'cover';
    }

    /** Last-resort cover when the model has no media at all. */
    protected function mediaCoverFallback(): ?string
    {
        return null;
    }

    /** Grid thumbnail: explicit cover, else first image row / YouTube thumb, else fallback. */
    public function coverUrl(): ?string
    {
        return $this->getFirstMediaUrl($this->mediaCoverCollection())
            ?: ($this->firstMediaItemCover() ?? $this->mediaCoverFallback());
    }

    /** Cover derived from the media array: first image row, else first YouTube thumbnail. */
    protected function firstMediaItemCover(): ?string
    {
        foreach ($this->mediaItems as $item) {
            if ($item->type === 'image' && ($url = $item->getFirstMediaUrl('file'))) {
                return $url;
            }
        }

        foreach ($this->mediaItems as $item) {
            if ($item->type === 'youtube' && ($thumb = $item->thumbnailUrl())) {
                return $thumb;
            }
        }

        return null;
    }

    /**
     * Ordered, render-ready media for the lightbox. Rows without a usable file
     * or a parseable YouTube URL are dropped so the front end never gets holes.
     *
     * @return list<array{type: string, url: string, caption: string|null}>
     */
    public function mediaPayload(): array
    {
        $out = [];

        foreach ($this->mediaItems as $item) {
            $url = $item->resolvedUrl();

            if (! $url) {
                continue;
            }

            $out[] = ['type' => $item->type, 'url' => $url, 'caption' => $item->caption];
        }

        return $out;
    }

    /**
     * Same rows as mediaPayload(), each carrying a grid-thumbnail URL: the image
     * itself, the YouTube poster, or null for video (the view renders a <video>
     * first-frame instead). Index-aligned with mediaPayload() so a gallery tile's
     * position maps straight to the lightbox item it opens.
     *
     * @return list<array{type: string, url: string, caption: string|null, thumb: string|null}>
     */
    public function mediaTiles(): array
    {
        $out = [];

        foreach ($this->mediaItems as $item) {
            $url = $item->resolvedUrl();

            if (! $url) {
                continue;
            }

            $thumb = match ($item->type) {
                'youtube' => $item->thumbnailUrl(),
                'video' => null,
                default => $url,
            };

            $out[] = ['type' => $item->type, 'url' => $url, 'caption' => $item->caption, 'thumb' => $thumb];
        }

        return $out;
    }
}
