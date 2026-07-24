<?php

namespace App\Observers;

use App\Models\MediaItem;
use Spatie\ResponseCache\Facades\ResponseCache;

class MediaItemObserver
{
    public function saved(MediaItem $m): void
    {
        ResponseCache::clear();
    }

    public function deleted(MediaItem $m): void
    {
        ResponseCache::clear();
    }
}
