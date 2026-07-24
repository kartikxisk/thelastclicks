<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Filament\Resources\QuoteResource\RelationManagers;
use App\Models\Quote;
use App\Models\User;
use Carbon\CarbonInterval;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 10;

    /** Unactioned leads, so the sidebar shows the size of the queue. */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->unactioned()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getEloquentQuery()->overdue()->exists() ? 'danger' : 'warning';
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Lead')
                ->columns(2)
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('email')->copyable(),
                    TextEntry::make('phone'),
                    TextEntry::make('company'),
                ]),
            Section::make('Brief')
                ->columns(3)
                ->schema([
                    TextEntry::make('project_type'),
                    TextEntry::make('budget'),
                    TextEntry::make('timeline'),
                    TextEntry::make('message')->columnSpanFull(),
                ]),
            Section::make('Workflow')
                ->columns(3)
                ->schema([
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'new' => 'gray',
                            'contacted' => 'warning',
                            'qualified' => 'info',
                            'won' => 'success',
                            'lost' => 'danger',
                            default => 'gray',
                        }),
                    TextEntry::make('assignee.name')->label('Assigned')->placeholder('Unassigned'),
                    TextEntry::make('contacted_at')
                        ->label('Responded in')
                        ->state(fn (Quote $record): string => $record->responseMinutes() === null
                            ? 'Not yet'
                            : CarbonInterval::minutes($record->responseMinutes())->cascade()->forHumans(short: true)),
                ]),
            Section::make('Timeline')
                ->description('Everything that has happened to this lead.')
                ->schema([
                    ViewEntry::make('timeline')
                        ->hiddenLabel()
                        ->view('filament.components.quote-timeline'),
                ]),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lead')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->required(),
                        TextInput::make('phone'),
                        TextInput::make('company'),
                    ]),
                Forms\Components\Section::make('Brief')
                    ->columns(3)
                    ->schema([
                        Select::make('project_type')->options([
                            'Brand film / commercial' => 'Brand film / commercial',
                            'Corporate event' => 'Corporate event',
                            'Product launch' => 'Product launch',
                            'Wedding' => 'Wedding',
                            'Editorial / photography' => 'Editorial / photography',
                            'Post-production only' => 'Post-production only',
                            'Other' => 'Other',
                        ]),
                        Select::make('budget')->options([
                            'Under ₹5L' => 'Under ₹5L',
                            '₹5L – ₹15L' => '₹5L – ₹15L',
                            '₹15L – ₹50L' => '₹15L – ₹50L',
                            '₹50L+' => '₹50L+',
                        ]),
                        Select::make('timeline')->options([
                            'Flexible' => 'Flexible',
                            'Within 2 weeks' => 'Within 2 weeks',
                            '1–2 months' => '1–2 months',
                            '3+ months' => '3+ months',
                        ]),
                        Textarea::make('message')->columnSpanFull()->rows(5),
                    ]),
                Forms\Components\Section::make('Workflow')
                    ->columns(2)
                    ->schema([
                        // Only the current stage and its legal next steps: the
                        // pipeline runs forwards only, and nothing returns to
                        // `new` — a closed lead comes back via Reopen.
                        Select::make('status')
                            ->options(fn (?Quote $record): array => collect(
                                $record ? [$record->status, ...$record->allowedTransitions()] : ['new']
                            )->mapWithKeys(fn (string $s): array => [$s => ucfirst($s)])->all())
                            ->helperText('New → Contacted → Qualified → Won. A lead can be lost at any open stage.')
                            ->required()
                            ->default('new'),
                        Select::make('assigned_to')
                            ->relationship('assignee', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Source')
                    ->columns(2)
                    ->schema([
                        TextInput::make('source_page')->disabled(),
                        TextInput::make('ip')->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('project_type')->toggleable(),
                TextColumn::make('budget')->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'gray',
                        'contacted' => 'warning',
                        'qualified' => 'info',
                        'won' => 'success',
                        'lost' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('assignee.name')->label('Assigned')->placeholder('Unassigned')->toggleable(),
                TextColumn::make('created_at')
                    ->label('Age')
                    ->since()
                    ->badge()
                    ->color(fn (Quote $record): string => match (true) {
                        $record->isOverdue() => 'danger',
                        $record->status === 'new' => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(fn (Quote $record): string => $record->created_at?->format('D j M Y, H:i') ?? '')
                    ->sortable(),
                TextColumn::make('contacted_at')
                    ->label('Responded in')
                    ->state(fn (Quote $record): string => $record->responseMinutes() === null
                        ? '—'
                        : CarbonInterval::minutes($record->responseMinutes())->cascade()->forHumans(short: true))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'won' => 'Won', 'lost' => 'Lost',
                ]),
                SelectFilter::make('assigned_to')->relationship('assignee', 'name'),
                Filter::make('overdue')
                    ->label('Overdue only')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->overdue()),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Received from'),
                        DatePicker::make('until')->label('Received until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('reopen')
                    ->label('Reopen')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (Quote $record): bool => $record->isClosed())
                    ->authorize(fn (Quote $record): bool => auth()->user()?->can('update', $record) ?? false)
                    ->form([
                        Textarea::make('comment')
                            ->label('Why is it back in play?')
                            ->rows(3),
                    ])
                    ->action(fn (Quote $record, array $data) => $record->reopen($data['comment'] ?? null, auth()->user()))
                    ->successNotificationTitle('Lead reopened'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('assign')
                        ->label('Assign to')
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            Select::make('assigned_to')
                                ->label('Owner')
                                ->options(fn () => User::query()->role(['Super-admin', 'Sales'])->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['assigned_to' => $data['assigned_to']]))
                        ->deselectRecordsAfterCompletion()
                        ->authorize(fn (): bool => auth()->user()?->can('update_quote') ?? false),
                    BulkAction::make('status')
                        ->label('Move stage')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('status')
                                ->label('Move to')
                                ->options(collect(Quote::STATUSES)->mapWithKeys(fn (string $s): array => [$s => ucfirst($s)])->all())
                                ->helperText('Leads that cannot legally reach this stage are skipped.')
                                ->required(),
                            Textarea::make('comment')->label('Comment (optional)')->rows(3),
                        ])
                        // Per record via transitionTo() so the hierarchy is enforced
                        // and QuoteObserver still stamps the lifecycle timestamps.
                        ->action(function (Collection $records, array $data): void {
                            $moved = $records->filter(fn (Quote $quote): bool => $quote->transitionTo(
                                $data['status'],
                                $data['comment'] ?? null,
                                auth()->user(),
                            ))->count();

                            $skipped = $records->count() - $moved;

                            Notification::make()
                                ->title($moved.' moved to '.$data['status'])
                                ->body($skipped > 0 ? $skipped.' skipped — the move would break the pipeline order.' : null)
                                ->{$skipped > 0 ? 'warning' : 'success'}()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->authorize(fn (): bool => auth()->user()?->can('update_quote') ?? false),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** @return Builder<Quote> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'view' => Pages\ViewQuote::route('/{record}'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}
