<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Generic cache-buster for models whose changes affect public pages but that have
 * no bespoke observer. Attached to Spatie's Media model — a hero/cover/logo upload
 * only writes a media row and may not dirty its parent, so the parent's observer
 * never fires and the cached HTML would keep serving the old (imageless) page.
 */
class ClearsResponseCacheObserver
{
    public function saved(Model $model): void
    {
        ResponseCache::clear();
    }

    public function deleted(Model $model): void
    {
        ResponseCache::clear();
    }
}
