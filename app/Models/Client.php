<?php

namespace App\Models;

use App\Models\Concerns\TouchesFrontend;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Client extends Model implements HasMedia
{
    use InteractsWithMedia, TouchesFrontend;

    protected $fillable = ['name', 'url', 'logo_path', 'order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Logos live on the media disk (S3 via MEDIA_DISK), same as every other upload. */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    /** @param  Builder<Client>  $q */
    public function scopeActive(Builder $q): void
    {
        $q->where('is_active', true);
    }

    /**
     * An uploaded logo wins; otherwise fall back to the path set in the admin,
     * which may be an absolute URL or a file bundled under public/.
     */
    public function logoUrl(): ?string
    {
        // logo_path holds an S3 key (e.g. "27/dlf.png") resolved against the media
        // disk, so the CloudFront host comes from config at runtime, not the DB. An
        // absolute URL stored there still passes through untouched.
        return $this->getFirstMediaUrl('logo') ?: MediaUrl::onMediaDisk($this->logo_path);
    }

    /**
     * The logo marquee runs on both pages.
     *
     * @return list<string>
     */
    public function frontendCacheTags(): array
    {
        return ['pages:home', 'pages:about'];
    }
}
