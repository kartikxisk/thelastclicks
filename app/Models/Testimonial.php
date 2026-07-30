<?php

namespace App\Models;

use App\Models\Concerns\TouchesFrontend;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory, TouchesFrontend;

    protected $fillable = ['industry_id', 'quote', 'client_name', 'role_company', 'order', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];

    /** @param Builder<Testimonial> $q
     * @return Builder<Testimonial>
     */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    /** @return BelongsTo<Industry, $this> */
    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    /**
     * Quotes are embedded in the industries payload as well as both pages.
     *
     * @return list<string>
     */
    public function frontendCacheTags(): array
    {
        return ['pages:home', 'pages:about', 'industries'];
    }
}
