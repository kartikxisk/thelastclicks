<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class LeadStatsWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $unactioned = $this->leads()->unactioned()->count();
        $overdue = $this->leads()->overdue()->count();

        $thisWeek = $this->leads()->where('created_at', '>=', now()->startOfWeek())->count();
        $lastWeek = $this->leads()
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->count();

        $wonThisMonth = $this->leads()
            ->where('status', 'won')
            ->where('closed_at', '>=', now()->startOfMonth())
            ->count();

        [$won, $lost] = $this->closedLast90Days();
        $decided = $won + $lost;
        $winRate = $decided > 0 ? round($won / $decided * 100) : 0;

        return [
            Stat::make('Needs action', (string) $unactioned)
                ->description($overdue > 0
                    ? $overdue.' past the '.Quote::slaHours().'h promise'
                    : 'All within the '.Quote::slaHours().'h promise')
                ->descriptionIcon($overdue > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($overdue > 0 ? 'danger' : ($unactioned > 0 ? 'warning' : 'success'))
                ->chart($this->dailyCounts(7)),

            Stat::make('Leads this week', (string) $thisWeek)
                ->description($this->deltaLabel($thisWeek, $lastWeek))
                ->descriptionIcon($thisWeek >= $lastWeek ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($thisWeek >= $lastWeek ? 'success' : 'gray')
                ->chart($this->dailyCounts(7)),

            Stat::make('Won this month', (string) $wonThisMonth)
                ->description('Closed since '.now()->startOfMonth()->format('M j'))
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),

            Stat::make('Win rate', $decided > 0 ? $winRate.'%' : '—')
                ->description($decided > 0
                    ? $won.' won of '.$decided.' decided (90d)'
                    : 'No leads decided yet')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($winRate >= 50 ? 'success' : 'warning'),
        ];
    }

    /** @return Builder<Quote> */
    protected function leads(): Builder
    {
        return Quote::query()->visibleTo(auth()->user());
    }

    /**
     * Won / lost among leads decided in the last 90 days.
     *
     * @return array{int, int}
     */
    protected function closedLast90Days(): array
    {
        $counts = $this->leads()
            ->whereIn('status', ['won', 'lost'])
            ->where('closed_at', '>=', now()->subDays(90))
            ->get(['status'])
            ->countBy('status');

        return [(int) ($counts['won'] ?? 0), (int) ($counts['lost'] ?? 0)];
    }

    /**
     * Daily new-lead counts for the sparkline. Bucketed in PHP rather than with
     * a raw DATE() so the query behaves the same on SQLite and MySQL.
     *
     * @return list<int>
     */
    protected function dailyCounts(int $days): array
    {
        $dates = $this->leads()
            ->where('created_at', '>=', today()->subDays($days - 1))
            ->pluck('created_at')
            ->countBy(fn ($date) => $date->toDateString());

        return collect(range($days - 1, 0))
            ->map(fn (int $ago) => (int) ($dates[today()->subDays($ago)->toDateString()] ?? 0))
            ->values()
            ->all();
    }

    protected function deltaLabel(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? 'First leads this week' : 'None last week either';
        }

        $change = round(($current - $previous) / $previous * 100);

        return ($change >= 0 ? '+' : '').$change.'% vs last week';
    }
}
