<?php

namespace App\Providers;

use App\Models\Crew;
use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Observers\CrewObserver;
use App\Observers\IndustryObserver;
use App\Observers\PortfolioObserver;
use App\Observers\PostObserver;
use App\Observers\ServiceObserver;
use App\Observers\SiteSettingObserver;
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
        Portfolio::observe(PortfolioObserver::class);
        Service::observe(ServiceObserver::class);
        Industry::observe(IndustryObserver::class);
        Crew::observe(CrewObserver::class);
        SiteSetting::observe(SiteSettingObserver::class);
    }
}
