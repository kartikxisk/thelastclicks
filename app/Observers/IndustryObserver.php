<?php

namespace App\Observers;

use App\Models\Industry;
use Spatie\ResponseCache\Facades\ResponseCache;

class IndustryObserver
{
    public function saved(Industry $i): void
    {
        ResponseCache::clear();
    }

    public function deleted(Industry $i): void
    {
        ResponseCache::clear();
    }
}
