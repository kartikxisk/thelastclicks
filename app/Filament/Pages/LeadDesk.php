<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AssigneeWorkloadWidget;
use App\Filament\Widgets\LeadStatsWidget;
use App\Filament\Widgets\LeadsTrendChart;
use App\Filament\Widgets\NeedsAttentionTable;
use App\Filament\Widgets\PipelineFunnelChart;
use App\Filament\Widgets\RecentActivityWidget;
use App\Models\Quote;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * The sales console. Access is governed by the Shield permission `page_LeadDesk`
 * so roles are managed from the panel rather than hardcoded here.
 */
class LeadDesk extends BaseDashboard
{
    use HasPageShield;

    protected static string $routePath = '/lead-desk';

    protected static ?string $title = 'Lead desk';

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 5;

    public function getColumns(): int|string|array
    {
        return 2;
    }

    /** Only the lead widgets live here — site widgets stay on the Dashboard. */
    public function getWidgets(): array
    {
        return [
            LeadStatsWidget::class,
            LeadsTrendChart::class,
            PipelineFunnelChart::class,
            NeedsAttentionTable::class,
            AssigneeWorkloadWidget::class,
            RecentActivityWidget::class,
        ];
    }

    public function getSubheading(): ?string
    {
        $overdue = Quote::query()->visibleTo(auth()->user())->overdue()->count();

        return $overdue > 0
            ? $overdue.' lead'.($overdue === 1 ? '' : 's').' past the '.Quote::slaHours().'-hour response promise.'
            : 'Every lead is inside the '.Quote::slaHours().'-hour response promise.';
    }
}
