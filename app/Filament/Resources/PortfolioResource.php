<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortfolioResource\Pages;
use App\Models\Portfolio;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PortfolioResource extends Resource
{
    protected static ?string $model = Portfolio::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                TextInput::make('client'),
                TextInput::make('year')->numeric()->minValue(2000)->maxValue(2100),
                Select::make('service_id')->relationship('service', 'title')->searchable()->preload(),
                Select::make('industry_id')->relationship('industry', 'title')->searchable()->preload(),
            ]),
            Section::make('Status & Owner')->columns(2)->schema([
                Select::make('status')->options([
                    'draft' => 'Draft', 'published' => 'Published',
                ])->required()->default('draft'),
                Select::make('owner_id')
                    ->relationship('owner', 'name')->searchable()->preload()
                    ->default(auth()->id())->required(),
            ]),
            RichEditor::make('body')->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('cover')
                ->collection('cover')
                ->image()
                ->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('gallery')
                ->collection('gallery')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'video/mp4'])
                ->maxSize(153600) // 150 MB — largest current film is ~65 MB
                ->multiple()
                ->reorderable()
                ->columnSpanFull(),
            Section::make('Design content')
                ->description('Fields used by the design-restored front-end templates.')
                ->columns(2)
                ->schema([
                    TextInput::make('location'),
                    TextInput::make('cover_url')->label('Cover URL')->url(),
                    TextInput::make('hero_html')->label('Hero HTML')->columnSpanFull(),
                    RichEditor::make('approach')->columnSpanFull(),
                    KeyValue::make('credits')
                        ->keyLabel('Role')
                        ->valueLabel('Name')
                        ->columnSpanFull(),
                    TagsInput::make('gallery_urls')
                        ->label('Gallery URLs')
                        ->helperText('Flat list of image URLs.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover'),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('client'),
                TextColumn::make('year')->sortable(),
                TextColumn::make('owner.name')->label('Owner'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('service_id')->relationship('service', 'title'),
                SelectFilter::make('industry_id')->relationship('industry', 'title'),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortfolios::route('/'),
            'create' => Pages\CreatePortfolio::route('/create'),
            'edit' => Pages\EditPortfolio::route('/{record}/edit'),
        ];
    }
}
