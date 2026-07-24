<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\MediaItemsRelationManager;
use App\Filament\Resources\WorkResource\Pages;
use App\Models\Work;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WorkResource extends Resource
{
    protected static ?string $model = Work::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Our Work';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                TextInput::make('client'),
                TextInput::make('year'),
                TextInput::make('order')->numeric()->default(0),
            ]),
            Textarea::make('summary')->rows(3)->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('cover')
                ->collection('cover')
                ->image()
                ->helperText('Optional. Falls back to the first image, then a YouTube thumbnail.')
                ->columnSpanFull(),
            Section::make()->columns(2)->schema([
                Toggle::make('is_published')->default(true),
                Toggle::make('is_featured')->label('Show on homepage'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover'),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('client')->searchable(),
                TextColumn::make('year')->sortable(),
                TextColumn::make('media_items_count')->counts('mediaItems')->label('Media'),
                IconColumn::make('is_published')->boolean(),
                IconColumn::make('is_featured')->boolean()->label('Homepage'),
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
            'index' => Pages\ListWorks::route('/'),
            'create' => Pages\CreateWork::route('/create'),
            'edit' => Pages\EditWork::route('/{record}/edit'),
        ];
    }
}
