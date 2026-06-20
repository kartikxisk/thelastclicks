<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Site';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
            ]),
            Textarea::make('hero_copy')->rows(2)->columnSpanFull(),
            RichEditor::make('body')->columnSpanFull(),
            SpatieMediaLibraryFileUpload::make('hero')
                ->collection('hero')
                ->image()
                ->columnSpanFull(),
            TextInput::make('order')->numeric()->default(0),
            TextInput::make('share')->label('Mix share (%)')->numeric()->minValue(0)->maxValue(100)
                ->helperText('Discipline % on the portfolio "mix of work" bars. Leave blank to hide.'),
            Section::make('Design content')
                ->description('Fields used by the design-restored front-end templates.')
                ->columns(2)
                ->schema([
                    TextInput::make('hero_headline')->label('Hero headline')->columnSpanFull(),
                    TextInput::make('hero_url')->label('Hero URL')->url(),
                    TextInput::make('featured_slug')->label('Featured slug'),
                    Fieldset::make('Proof')
                        ->columns(3)
                        ->schema([
                            TextInput::make('proof.count')->label('Count'),
                            TextInput::make('proof.label')->label('Label'),
                            TextInput::make('proof.sectors')->label('Sectors'),
                        ]),
                    Fieldset::make('Call to action')
                        ->columns(1)
                        ->schema([
                            TextInput::make('cta.title')->label('Title'),
                            Textarea::make('cta.copy')->label('Copy')->rows(2),
                            TextInput::make('cta.prefill')->label('Prefill'),
                        ]),
                    Repeater::make('hero_meta')
                        ->label('Hero meta')
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            TextInput::make('label'),
                            TextInput::make('value'),
                        ]),
                    Repeater::make('pillars')
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('title'),
                            Textarea::make('desc')->rows(2),
                        ]),
                    Repeater::make('phases')
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            TextInput::make('num')->label('No.'),
                            TextInput::make('title'),
                            Textarea::make('desc')->rows(2)->columnSpanFull(),
                            TextInput::make('time'),
                        ]),
                    Repeater::make('kit')
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('title'),
                            TagsInput::make('items')->helperText('Flat list of kit items.'),
                        ]),
                    Repeater::make('faqs')
                        ->label('FAQs')
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('q')->label('Question'),
                            Textarea::make('a')->label('Answer')->rows(2),
                        ]),
                    TagsInput::make('tags')
                        ->helperText('Flat list of tags.')
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
