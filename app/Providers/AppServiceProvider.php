<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\HeroSlide;
use App\Models\Industry;
use App\Models\MediaItem;
use App\Models\Post;
use App\Models\Quote;
use App\Models\SeoPage;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\Work;
use App\Observers\ClearsResponseCacheObserver;
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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

        // Client edits (name/order/active) and EVERY Spatie media upload — hero,
        // cover, logo, gallery — must bust the cached HTML. A media-only change
        // doesn't dirty its parent model, so the parent observer alone isn't enough.
        Client::observe(ClearsResponseCacheObserver::class);
        Media::observe(ClearsResponseCacheObserver::class);

        // Hero slides too: the Media observer above catches the upload, but
        // toggling a slide off, reordering, or deleting a row never touches a
        // media record, so the homepage would keep serving the cached hero.
        HeroSlide::observe(ClearsResponseCacheObserver::class);
    }
}
