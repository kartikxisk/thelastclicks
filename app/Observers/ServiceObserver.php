<?php

namespace App\Observers;

use App\Models\Service;
use Spatie\ResponseCache\Facades\ResponseCache;

class ServiceObserver
{
    public function saved(Service $s): void
    {
        ResponseCache::clear();
    }

    public function deleted(Service $s): void
    {
        ResponseCache::clear();
    }
}
