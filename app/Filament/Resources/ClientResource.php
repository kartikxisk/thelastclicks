<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Site';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Client logos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255)
                    ->helperText('Used as the logo\'s alt text.'),
                TextInput::make('url')->label('Website URL')->url()->maxLength(255),
            ]),
            SpatieMediaLibraryFileUpload::make('logo')
                ->collection('logo')
                ->image()
                ->helperText('Transparent PNG or SVG. The strip renders every logo white, so colour is stripped. Takes priority over the path below.')
                ->columnSpanFull(),
            TextInput::make('logo_path')
                ->label('Or logo path / URL')
                ->maxLength(255)
                ->placeholder('clients/bmw.png')
                ->helperText('Used when nothing is uploaded — a file bundled under public/ (e.g. clients/bmw.png) or a full https:// URL.')
                ->columnSpanFull(),
            TextInput::make('order')->numeric()->default(0),
            Toggle::make('is_active')->default(true)
                ->helperText('Hide without deleting.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable(),
                SpatieMediaLibraryImageColumn::make('logo')->collection('logo')->label('Uploaded'),
                // Distinct name: a second column called `logo_path` would collide
                // with the Source badge below and only one would render.
                ImageColumn::make('logo_preview')
                    ->label('Logo')
                    ->state(fn (Client $record): ?string => $record->logoUrl())
                    ->extraImgAttributes(['class' => 'object-contain bg-white/5 p-1 rounded']),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('logo_path')
                    ->label('Source')
                    ->badge()
                    ->formatStateUsing(fn (?string $state, Client $record): string => match (true) {
                        (bool) $record->getFirstMedia('logo') => 'Uploaded',
                        filled($state) => 'Path',
                        default => 'None',
                    })
                    ->color(fn (?string $state, Client $record): string => match (true) {
                        (bool) $record->getFirstMedia('logo') => 'success',
                        filled($state) => 'info',
                        default => 'danger',
                    }),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
