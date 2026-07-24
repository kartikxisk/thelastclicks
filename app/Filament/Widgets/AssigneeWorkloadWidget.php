<?php

namespace App\Filament\Widgets;

use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AssigneeWorkloadWidget extends TableWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Who is carrying what';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    /**
     * Shield's permission governs access, but a Sales user only ever sees their
     * own leads — a cross-team workload board would tell them nothing.
     */
    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user?->can(static::getPermissionName())) {
            return false;
        }

        return ! ($user->hasRole('Sales') && ! $user->hasRole('Super-admin'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->handlers())
            ->defaultSort('open_leads', 'desc')
            ->paginated([5, 10])
            ->emptyStateHeading('No lead handlers yet')
            ->emptyStateDescription('Give a user the Sales role to see workload here.')
            ->columns([
                TextColumn::make('name')->weight('bold'),
                TextColumn::make('open_leads')
                    ->label('Open')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray')
                    ->sortable(),
                TextColumn::make('overdue_leads')
                    ->label('Overdue')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'success')
                    ->sortable(),
            ]);
    }

    /** @return Builder<User> */
    protected function handlers(): Builder
    {
        return User::query()
            ->role(['Super-admin', 'Sales'])
            ->withCount([
                'assignedQuotes as open_leads' => fn (Builder $q) => $q->open(),
                'assignedQuotes as overdue_leads' => fn (Builder $q) => $q->overdue(),
            ]);
    }
}
