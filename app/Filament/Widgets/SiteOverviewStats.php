<?php

namespace App\Filament\Widgets;

use App\Models\Industry;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\Work;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $publishedPosts = Post::query()->published()->count();
        $draftPosts = Post::query()->count() - $publishedPosts;

        $publishedWork = Work::query()->published()->count();
        $draftWork = Work::query()->count() - $publishedWork;

        return [
            Stat::make('Journal posts', (string) $publishedPosts)
                ->description($draftPosts > 0 ? $draftPosts.' unpublished' : 'All published')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($draftPosts > 0 ? 'warning' : 'success'),

            Stat::make('Work published', (string) $publishedWork)
                ->description($draftWork > 0 ? $draftWork.' unpublished' : 'All published')
                ->descriptionIcon('heroicon-m-film')
                ->color($draftWork > 0 ? 'warning' : 'success'),

            Stat::make('Industries', (string) Industry::query()->count())
                ->description('Verticals on the site')
                ->descriptionIcon('heroicon-m-rectangle-group')
                ->color('info'),

            Stat::make('Subscribers', (string) Subscriber::query()->count())
                ->description('Journal mailing list')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('info'),
        ];
    }
}
