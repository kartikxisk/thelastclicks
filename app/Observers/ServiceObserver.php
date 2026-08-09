<?php

namespace App\Observers;

use App\Jobs\SubmitToIndexNow;
use App\Models\Service;
use Spatie\ResponseCache\Facades\ResponseCache;

class ServiceObserver
{
    public function saved(Service $s): void
    {
        ResponseCache::clear();

        // Service pages appear on the homepage as well as at their own URL, so
        // both are worth pinging. Inert without an IndexNow key.
        SubmitToIndexNow::dispatch([
            url('/services/'.$s->slug),
            url('/'),
        ]);
    }

    public function deleted(Service $s): void
    {
        ResponseCache::clear();
    }
}
