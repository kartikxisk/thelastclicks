<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\QuoteResource;
use App\Models\Quote;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class NeedsAttentionTable extends TableWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Needs attention';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->openLeads())
            // Oldest first: the longest-waiting lead is the most urgent.
            ->defaultSort('created_at', 'asc')
            ->paginationPageOptions([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Nothing waiting')
            ->emptyStateDescription('Every lead has been actioned.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->recordUrl(fn (Quote $record): string => QuoteResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('name')->searchable()->weight('bold'),
                TextColumn::make('company')->toggleable()->placeholder('—'),
                TextColumn::make('project_type')->label('Brief')->toggleable()->placeholder('—'),
                TextColumn::make('budget')->toggleable()->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'new' ? 'gray' : 'warning'),
                TextColumn::make('created_at')
                    ->label('Waiting')
                    ->since()
                    ->badge()
                    ->color(fn (Quote $record): string => $record->isOverdue() ? 'danger' : 'warning')
                    ->tooltip(fn (Quote $record): string => $record->isOverdue()
                        ? 'Past the '.Quote::slaHours().'h response promise'
                        : 'Due by '.$record->slaDueAt()?->format('D j M, H:i'))
                    ->sortable(),
                TextColumn::make('assignee.name')->label('Owner')->placeholder('Unassigned')->toggleable(),
            ]);
    }

    /** @return Builder<Quote> */
    protected function openLeads(): Builder
    {
        return Quote::query()
            ->visibleTo(auth()->user())
            ->whereIn('status', ['new', 'contacted'])
            ->with('assignee');
    }
}
