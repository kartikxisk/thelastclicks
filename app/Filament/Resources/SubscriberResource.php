<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriberResource\Pages;
use App\Models\Subscriber;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Site';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Subscribers';

    /** Subscribers arrive from the site's newsletter form — never hand-created here. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->searchable()->sortable()->copyable(),
                TextColumn::make('source_page')->label('Source')->searchable()->toggleable(),
                TextColumn::make('created_at')->label('Subscribed')->dateTime()->sortable(),
                TextColumn::make('unsubscribed_at')->label('Unsubscribed')->dateTime()
                    ->placeholder('—')->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('active')
                    ->label('Active only')
                    ->query(fn (Builder $q) => $q->whereNull('unsubscribed_at')),
            ])
            ->actions([
                Action::make('toggleSubscription')
                    ->label(fn (Subscriber $record) => $record->unsubscribed_at ? 'Resubscribe' : 'Unsubscribe')
                    ->icon(fn (Subscriber $record) => $record->unsubscribed_at ? 'heroicon-o-arrow-path' : 'heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->action(fn (Subscriber $record) => $record->update([
                        'unsubscribed_at' => $record->unsubscribed_at ? null : now(),
                    ])),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscribers::route('/'),
        ];
    }
}
