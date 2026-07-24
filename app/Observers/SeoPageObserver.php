<?php

namespace App\Observers;

use App\Models\SeoPage;
use Spatie\ResponseCache\Facades\ResponseCache;

// SEO rows are rendered into cached HTML, so edits must flush the response cache
// or the admin's change wouldn't appear until the cache expired.
class SeoPageObserver
{
    public function saved(SeoPage $page): void
    {
        ResponseCache::clear();
    }

    public function deleted(SeoPage $page): void
    {
        ResponseCache::clear();
    }
}
