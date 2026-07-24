<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RecentContentTable;
use App\Filament\Widgets\SiteOverviewStats;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Panel landing page: a plain overview of the site's content. Lead widgets live
 * on the LeadDesk page instead, so this stays useful for editors who have no
 * access to enquiries.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getColumns(): int|string|array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            SiteOverviewStats::class,
            RecentContentTable::class,
        ];
    }

    public function getSubheading(): ?string
    {
        return 'What is live on thelastclicks.com right now.';
    }
}
