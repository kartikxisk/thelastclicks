<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\MediaItemsRelationManager;
use App\Filament\Resources\WorkResource\Pages;
use App\Models\Work;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WorkResource extends Resource
{
    protected static ?string $model = Work::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Portfolio';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                TextInput::make('client'),
                TextInput::make('year'),
                Select::make('category')
                    ->options(Work::CATEGORIES)
                    ->helperText('Drives the filter on the Portfolio page.'),
                TextInput::make('order')->numeric()->default(0),
            ]),
            Textarea::make('summary')->rows(3)->columnSpanFull(),

            Section::make('Credits')->schema([
                CheckboxList::make('crafts')
                    ->options(Work::CRAFTS)
                    ->columns(3)
                    ->helperText('Which of these we did in-house on this project. Also a filter on the Portfolio page — this is the only place on the site where the in-house post claim is actually evidenced.'),
                Repeater::make('credits')
                    ->schema([
                        TextInput::make('role')->placeholder('Director')->required(),
                        TextInput::make('name')->placeholder('Full name')->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Add credit')
                    ->helperText('Named roles: director, DOP, editor, colourist.'),
            ])->columns(1)->collapsed(),

            Section::make('Context')->columns(2)->schema([
                TextInput::make('location')->placeholder('Udaipur, Rajasthan'),
                TextInput::make('agency')->placeholder('Agency or brand team, if any'),
            ])->collapsed(),

            SpatieMediaLibraryFileUpload::make('cover')
                ->collection('cover')
                ->image()
                ->helperText('Optional. Falls back to the first image, then a YouTube thumbnail.')
                ->columnSpanFull(),
            TextInput::make('preview_video_url')
                ->url()
                ->label('Grid preview loop')
                ->helperText('Optional. Short muted MP4 that plays on the grid tile when hovered. Leave blank to use the first uploaded video below. Keep it under ~6s — a full film is far too heavy to autoplay in a grid.')
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
                TextColumn::make('category')->badge()
                    ->formatStateUsing(fn ($state) => Work::CATEGORIES[$state] ?? $state)
                    ->placeholder('—'),
                TextColumn::make('year')->sortable(),
                TextColumn::make('media_items_count')->counts('mediaItems')->label('Media'),
                IconColumn::make('is_published')->boolean(),
                IconColumn::make('is_featured')->boolean()->label('Homepage'),
            ])
            ->filters([
                SelectFilter::make('category')->options(Work::CATEGORIES),
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
