<?php

namespace App\Providers;

use App\Models\Industry;
use App\Models\MediaItem;
use App\Models\Post;
use App\Models\Quote;
use App\Models\SeoPage;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\Work;
use App\Observers\IndustryObserver;
use App\Observers\MediaItemObserver;
use App\Observers\PostObserver;
use App\Observers\QuoteObserver;
use App\Observers\SeoPageObserver;
use App\Observers\ServiceObserver;
use App\Observers\SiteSettingObserver;
use App\Observers\TestimonialObserver;
use App\Observers\WorkObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Post::observe(PostObserver::class);
        SeoPage::observe(SeoPageObserver::class);
        Service::observe(ServiceObserver::class);
        Industry::observe(IndustryObserver::class);
        SiteSetting::observe(SiteSettingObserver::class);
        Testimonial::observe(TestimonialObserver::class);
        Work::observe(WorkObserver::class);
        MediaItem::observe(MediaItemObserver::class);
        Quote::observe(QuoteObserver::class);
    }
}
