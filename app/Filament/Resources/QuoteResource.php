<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Filament\Resources\QuoteResource\RelationManagers;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Leads';

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
                ->columns(2)
                ->schema([
                    TextEntry::make('status')->badge(),
                    TextEntry::make('assignee.name')->label('Assigned'),
                ]),
            Section::make('Activity Log')
                ->schema([
                    ViewEntry::make('activities')
                        ->view('filament.components.quote-activity-feed'),
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
                        Select::make('status')->options([
                            'new' => 'New',
                            'contacted' => 'Contacted',
                            'qualified' => 'Qualified',
                            'won' => 'Won',
                            'lost' => 'Lost',
                        ])->required()->default('new'),
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
                TextColumn::make('assignee.name')->label('Assigned')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'won' => 'Won', 'lost' => 'Lost',
                ]),
                SelectFilter::make('assigned_to')->relationship('assignee', 'name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** @return Builder<Quote> */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user && $user->hasRole('Sales') && ! $user->hasRole('Super-admin')) {
            $query->where('assigned_to', $user->id);
        }

        return $query;
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
