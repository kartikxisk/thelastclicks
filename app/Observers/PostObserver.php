<?php

namespace App\Observers;

use App\Models\Post;
use Spatie\ResponseCache\Facades\ResponseCache;

class PostObserver
{
    public function saved(Post $post): void
    {
        ResponseCache::clear();
    }

    public function deleted(Post $post): void
    {
        ResponseCache::clear();
    }
}
