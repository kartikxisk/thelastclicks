<?php

namespace App\Observers;

use App\Models\Crew;
use Spatie\ResponseCache\Facades\ResponseCache;

class CrewObserver
{
    public function saved(Crew $c): void
    {
        ResponseCache::clear();
    }

    public function deleted(Crew $c): void
    {
        ResponseCache::clear();
    }
}
