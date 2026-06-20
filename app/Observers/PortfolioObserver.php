<?php

namespace App\Observers;

use App\Models\Portfolio;
use Spatie\ResponseCache\Facades\ResponseCache;

class PortfolioObserver
{
    public function saved(Portfolio $p): void
    {
        ResponseCache::clear();
    }

    public function deleted(Portfolio $p): void
    {
        ResponseCache::clear();
    }
}
