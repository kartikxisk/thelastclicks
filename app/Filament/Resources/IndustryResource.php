<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\MediaItemsRelationManager;
use App\Filament\Resources\IndustryResource\Pages;
use App\Models\Industry;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class IndustryResource extends Resource
{
    protected static ?string $model = Industry::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
            ]),
            TextInput::make('summary')->columnSpanFull(),
            // The same link WorkResource edits, from this end — curating a set
            // is natural here, filing one project is natural there.
            Select::make('works')
                ->label('Work filed under this industry')
                ->relationship('works', 'title')
                ->multiple()
                ->searchable()
                ->preload()
                ->columnSpanFull()
                ->helperText('Drives the industry chips on the portfolio page. Unpublished projects stay listed here but do not reach the public grid.'),
            RichEditor::make('body')->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('hero')
                ->collection('hero')
                ->image()
                ->columnSpanFull(),
            TextInput::make('order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [MediaItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIndustries::route('/'),
            'create' => Pages\CreateIndustry::route('/create'),
            'edit' => Pages\EditIndustry::route('/{record}/edit'),
        ];
    }
}
