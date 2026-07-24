<?php

namespace App\Observers;

use App\Models\Testimonial;
use Spatie\ResponseCache\Facades\ResponseCache;

class TestimonialObserver
{
    public function saved(Testimonial $t): void
    {
        ResponseCache::clear();
    }

    public function deleted(Testimonial $t): void
    {
        ResponseCache::clear();
    }
}
