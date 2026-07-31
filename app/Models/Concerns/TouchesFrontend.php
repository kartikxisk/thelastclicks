<?php

namespace App\Models\Concerns;

use App\Jobs\RevalidateFrontend;

/**
 * Invalidates the frontend's ISR cache when admin-managed content changes.
 *
 * Adopting models declare frontendCacheTags(). Structural on purpose: a model
 * that uses the trait cannot forget to register the observer, which is exactly
 * the failure mode that leaves an editor staring at stale content.
 */
trait TouchesFrontend
{
    public static function bootTouchesFrontend(): void
    {
        $dispatch = function ($model): void {
            RevalidateFrontend::dispatch($model->frontendCacheTags());
        };

        static::saved($dispatch);
        static::deleted($dispatch);
    }

    /**
     * Cache tags this record's content appears under. Include both the
     * collection tag and the per-slug tag, plus any page bundle that embeds
     * this model.
     *
     * @return list<string>
     */
    abstract public function frontendCacheTags(): array;
}
