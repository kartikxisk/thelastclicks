<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Hero';

    protected static ?string $modelLabel = 'hero slide';

    protected static ?string $pluralModelLabel = 'hero slides';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('label')
                    ->helperText('Admin-only name so you can tell slides apart. Not shown on the site.'),

                SpatieMediaLibraryFileUpload::make('asset')
                    ->collection('asset')
                    ->acceptedFileTypes(['image/*', 'video/mp4', 'video/webm', 'video/quicktime'])
                    ->maxSize(120 * 1024)
                    ->helperText('Image or video. Landscape, 1920px wide or larger. Keep films under ~15s and muted — they play silently on loop.')
                    ->required()
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('poster')
                    ->collection('poster')
                    ->image()
                    ->helperText('Video slides only: the frame shown while the film loads. Ignored for image slides.')
                    ->columnSpanFull(),
            ]),
            Section::make()->columns(2)->schema([
                TextInput::make('order')->numeric()->default(0)
                    ->helperText('Low to high. One active slide behaves like a single background; two or more cross-fade.'),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('poster')->collection('poster')->label('Poster'),
                TextColumn::make('label')->searchable()->placeholder('—'),
                TextColumn::make('kind')->label('Type')
                    ->badge()
                    ->state(fn (HeroSlide $record) => $record->isVideo() ? 'Video' : 'Image'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
