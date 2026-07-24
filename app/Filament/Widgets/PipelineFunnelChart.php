<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class PipelineFunnelChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Pipeline';

    protected static ?string $description = 'Where every lead currently sits.';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $counts = Quote::query()
            ->visibleTo(auth()->user())
            ->get(['status'])
            ->countBy('status');

        return [
            'datasets' => [[
                'label' => 'Leads',
                'data' => collect(Quote::STATUSES)->map(fn (string $s) => (int) ($counts[$s] ?? 0))->all(),
                'backgroundColor' => ['#6b7280', '#f59e0b', '#3b82f6', '#22c55e', '#ef4444'],
                'borderRadius' => 6,
            ]],
            'labels' => collect(Quote::STATUSES)->map(fn (string $s) => ucfirst($s))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
            'plugins' => ['legend' => ['display' => false]],
        ];
    }
}
