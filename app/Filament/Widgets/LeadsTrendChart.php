<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class LeadsTrendChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Leads over time';

    protected static ?string $description = 'New enquiries per day, last 30 days.';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected static ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = 30;

        // Bucketed in PHP so the query is identical on SQLite and MySQL.
        $counts = Quote::query()
            ->visibleTo(auth()->user())
            ->where('created_at', '>=', today()->subDays($days - 1))
            ->pluck('created_at')
            ->countBy(fn ($date) => $date->toDateString());

        $labels = [];
        $values = [];

        foreach (range($days - 1, 0) as $ago) {
            $day = today()->subDays($ago);
            $labels[] = $day->format('j M');
            $values[] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Leads',
                'data' => $values,
                'borderColor' => '#e80f03',
                'backgroundColor' => 'rgba(232, 15, 3, 0.15)',
                'fill' => true,
                'tension' => 0.35,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                // Lead counts are whole numbers — stop Chart.js inventing 0.5 gridlines.
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
            'plugins' => ['legend' => ['display' => false]],
        ];
    }
}
