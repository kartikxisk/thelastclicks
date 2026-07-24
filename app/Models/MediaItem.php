<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MediaItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'media_items';

    protected $fillable = ['mediable_type', 'mediable_id', 'type', 'youtube_url', 'caption', 'order'];

    /** @return MorphTo<Model, $this> */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile();
    }

    /** The 11-character YouTube id, or null when the URL is absent/unparseable. */
    public function youtubeId(): ?string
    {
        if (! $this->youtube_url) {
            return null;
        }

        preg_match(
            '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~',
            $this->youtube_url,
            $m
        );

        return $m[1] ?? null;
    }

    public function embedUrl(): ?string
    {
        $id = $this->youtubeId();

        return $id ? "https://www.youtube-nocookie.com/embed/{$id}" : null;
    }

    public function thumbnailUrl(): ?string
    {
        $id = $this->youtubeId();

        return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : null;
    }

    /** Playable/displayable URL for this row, or null when nothing usable is attached. */
    public function resolvedUrl(): ?string
    {
        if ($this->type === 'youtube') {
            return $this->embedUrl();
        }

        return $this->getFirstMediaUrl('file') ?: null;
    }
}
