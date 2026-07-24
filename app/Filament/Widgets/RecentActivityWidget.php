<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class RecentActivityWidget extends TableWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Recent lead activity';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->activity())
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10])
            ->emptyStateHeading('No activity yet')
            ->emptyStateDescription('Status and owner changes will show up here.')
            ->columns([
                TextColumn::make('subject.name')->label('Lead')->placeholder('Deleted lead')->weight('bold'),
                TextColumn::make('changes')
                    ->label('Change')
                    ->formatStateUsing(fn (Activity $record): string => $this->describe($record)),
                TextColumn::make('causer.name')->label('By')->placeholder('System'),
                TextColumn::make('created_at')->label('When')->since(),
            ]);
    }

    /** @return Builder<Activity> */
    protected function activity(): Builder
    {
        /** @var Builder<Activity> $query */
        $query = Activity::query()
            ->where('subject_type', Quote::class)
            // Sales users must not see movement on leads that aren't theirs.
            ->whereIn('subject_id', Quote::query()->visibleTo(auth()->user())->select('id'))
            ->with(['subject', 'causer']);

        return $query;
    }

    /** Turn the logged attribute diff into one readable line. */
    protected function describe(Activity $activity): string
    {
        $changes = $activity->changes();
        $after = $changes['attributes'] ?? [];
        $before = $changes['old'] ?? [];

        $parts = [];

        if (array_key_exists('status', $after)) {
            $parts[] = 'status '.($before['status'] ?? '—').' → '.$after['status'];
        }

        if (array_key_exists('assigned_to', $after)) {
            $parts[] = $after['assigned_to'] === null ? 'unassigned' : 'reassigned';
        }

        return $parts === [] ? ucfirst((string) $activity->description) : implode(', ', $parts);
    }
}
