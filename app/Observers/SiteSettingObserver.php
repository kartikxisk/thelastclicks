<?php

namespace App\Observers;

use App\Models\SiteSetting;
use Spatie\ResponseCache\Facades\ResponseCache;

class SiteSettingObserver
{
    public function saved(SiteSetting $s): void
    {
        ResponseCache::clear();
    }

    public function deleted(SiteSetting $s): void
    {
        ResponseCache::clear();
    }
}
