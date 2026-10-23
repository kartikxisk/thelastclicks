<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLocation extends Model
{
    protected $fillable = [
        'service_id', 'city_slug', 'city_name', 'headline', 'intro', 'sections', 'highlights', 'faqs',
        'meta_title', 'meta_description', 'is_published', 'order',
    ];

    protected $casts = [
        'sections' => 'array',
        'highlights' => 'array',
        'faqs' => 'array',
        'is_published' => 'boolean',
    ];

    /**
     * @param  Builder<ServiceLocation>  $query
     * @return Builder<ServiceLocation>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** Needs the service loaded, or it costs a query per call. */
    public function url(): string
    {
        return url('/services/'.$this->service->slug.'/'.$this->city_slug);
    }
}
