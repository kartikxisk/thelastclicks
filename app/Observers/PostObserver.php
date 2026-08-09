<?php

namespace App\Observers;

use App\Jobs\SubmitToIndexNow;
use App\Models\Post;
use Spatie\ResponseCache\Facades\ResponseCache;

class PostObserver
{
    public function saved(Post $post): void
    {
        ResponseCache::clear();

        // Ping IndexNow with the post's own URL and the index it appears on. The
        // job no-ops without a key or on a local APP_URL, so this is inert until
        // the site is actually configured for it.
        SubmitToIndexNow::dispatch([
            url('/blog/'.$post->slug),
            url('/blog'),
        ]);
    }

    public function deleted(Post $post): void
    {
        ResponseCache::clear();
    }
}
