<?php

namespace App\Observers;

use App\Models\Work;
use Spatie\ResponseCache\Facades\ResponseCache;

class WorkObserver
{
    public function saved(Work $w): void
    {
        ResponseCache::clear();
    }

    public function deleted(Work $w): void
    {
        ResponseCache::clear();
    }
}
