<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Client extends Model implements HasMedia
{
    use InteractsWithMedia;

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
        return $this->getFirstMediaUrl('logo') ?: MediaUrl::asset($this->logo_path);
    }
}
