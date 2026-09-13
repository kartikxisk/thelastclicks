<?php

namespace App\Filament\Resources;

use App\Filament\Imports\OutreachContactImporter;
use App\Filament\Resources\OutreachContactResource\Pages;
use App\Models\OutreachContact;
use App\Models\OutreachSuppression;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class OutreachContactResource extends Resource
{
    protected static ?string $model = OutreachContact::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Leads';

    protected static ?string $navigationLabel = 'Outreach';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'organizer';

    /** Colour the whole pipeline once, so the table and the view page agree. */
    public static function statusColour(?string $status): string
    {
        return match ($status) {
            'Not Contacted' => 'gray',
            'Email Sent', 'Follow-up 1', 'Follow-up 2', 'Follow-up 3' => 'warning',
            'Replied', 'In Discussion' => 'info',
            'Quote Sent' => 'primary',
            'Won' => 'success',
            'Lost', 'Bounced', 'Not Relevant' => 'danger',
            default => 'gray',
        };
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Contact')->columns(2)->schema([
                Forms\Components\TextInput::make('email')
                    ->email()->required()->unique(ignoreRecord: true)
                    ->helperText('Matching on a re-upload is done on this address.'),
                Forms\Components\TextInput::make('organizer')->maxLength(255),
                Forms\Components\TextInput::make('organizer_short')
                    ->label('Greeting name')
                    ->helperText('Used as "Hi {name}," — leave blank to derive it from the organizer.')
                    ->maxLength(255),
                Forms\Components\TextInput::make('owner')->maxLength(255),
            ]),

            Forms\Components\Section::make('Event')->columns(3)->schema([
                Forms\Components\TextInput::make('event')->maxLength(255)->columnSpan(2),
                Forms\Components\TextInput::make('event_city')->maxLength(255),
                Forms\Components\TextInput::make('event_dates')->maxLength(255),
                Forms\Components\DatePicker::make('event_starts_on'),
                Forms\Components\TextInput::make('event_count')->numeric()->default(1),
                Forms\Components\TextInput::make('locations')->maxLength(255)->columnSpan(3),
            ]),

            Forms\Components\Section::make('Pipeline')->columns(3)->schema([
                Forms\Components\Select::make('status')
                    ->options(array_combine(OutreachContact::STATUSES, OutreachContact::STATUSES))
                    ->default('Not Contacted')->required()
                    ->helperText('Anything outside the first three stops the sequence for good.'),
                Forms\Components\Select::make('priority')
                    ->options(['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low']),
                Forms\Components\DatePicker::make('next_action_on')
                    ->helperText('What the "Due now" filter reads.'),
                Forms\Components\DatePicker::make('email_sent_on'),
                Forms\Components\DatePicker::make('follow_up_1_on'),
                Forms\Components\DatePicker::make('follow_up_2_on'),
                Forms\Components\DatePicker::make('last_contact'),
                Forms\Components\Toggle::make('replied')->inline(false),
                Forms\Components\TextInput::make('outcome')->maxLength(255),
                Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('next_action_on')
            ->columns([
                Tables\Columns\TextColumn::make('organizer')
                    ->searchable()->sortable()->wrap()
                    ->description(fn (OutreachContact $r) => $r->email),

                Tables\Columns\TextColumn::make('event')
                    ->searchable()->wrap()->toggleable()
                    ->description(fn (OutreachContact $r) => $r->event_city),

                Tables\Columns\TextColumn::make('status')
                    ->badge()->sortable()
                    ->color(fn (?string $state) => static::statusColour($state)),

                Tables\Columns\TextColumn::make('next_action_on')
                    ->label('Next action')->date('d M Y')->sortable()
                    ->color(fn (OutreachContact $r) => $r->next_action_on?->isPast() ? 'danger' : null),

                Tables\Columns\TextColumn::make('sends_count')
                    ->label('Sent')->counts('sends')->badge()->color('gray'),

                Tables\Columns\IconColumn::make('replied')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('owner')->toggleable()->searchable(),
                Tables\Columns\TextColumn::make('event_starts_on')
                    ->label('Event')->date('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(array_combine(OutreachContact::STATUSES, OutreachContact::STATUSES))
                    ->multiple(),

                Tables\Filters\Filter::make('due')
                    ->label('Due now')
                    ->query(fn ($query) => $query->due()
                        ->where(fn ($q) => $q->whereNull('next_action_on')
                            ->orWhereDate('next_action_on', '<=', now()))),

                Tables\Filters\Filter::make('never_contacted')
                    ->label('Never contacted')
                    ->query(fn ($query) => $query->where('status', 'Not Contacted')),

                Tables\Filters\TernaryFilter::make('replied'),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(OutreachContactImporter::class)
                    ->label('Upload sheet')
                    ->color('primary'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('setStatus')
                    ->label('Set status')
                    ->icon('heroicon-o-flag')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(array_combine(OutreachContact::STATUSES, OutreachContact::STATUSES))
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $records->each->update(['status' => $data['status']]);

                        Notification::make()
                            ->title($records->count().' contact(s) updated')
                            ->success()->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('suppress')
                    ->label('Opt out')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Adds these addresses to the suppression list. They are checked immediately before every send, so this takes effect even mid-run.')
                    ->action(function (Collection $records): void {
                        /** @var OutreachContact $record */
                        foreach ($records as $record) {
                            OutreachSuppression::firstOrCreate(
                                ['email' => $record->email],
                                ['reason' => 'Opted out from admin'],
                            );
                            $record->update(['status' => 'Not Relevant']);
                        }

                        Notification::make()
                            ->title($records->count().' address(es) suppressed')
                            ->success()->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No contacts yet')
            ->emptyStateDescription('Upload a .csv or .xlsx with at least an email column.');
    }

    public static function getRelations(): array
    {
        return [
            OutreachContactResource\RelationManagers\SendsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutreachContacts::route('/'),
            'create' => Pages\CreateOutreachContact::route('/create'),
            'edit' => Pages\EditOutreachContact::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $due = static::getModel()::query()->due()
            ->where(fn ($q) => $q->whereNull('next_action_on')->orWhereDate('next_action_on', '<=', now()))
            ->count();

        return $due > 0 ? (string) $due : null;
    }
}
