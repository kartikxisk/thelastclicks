<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'mediaItems';

    protected static ?string $title = 'Media';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('type')
                ->options([
                    'image' => 'Image upload',
                    'video' => 'Video upload',
                    'youtube' => 'YouTube embed',
                ])
                ->default('image')
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if (! in_array($state, ['image', 'video'], true)) {
                        $set('file', null);
                    }
                }),
            // One upload bound to the single `file` collection; the accepted
            // types follow the chosen row type. (Two components pointing at the
            // same collection would fight over the same state path.)
            SpatieMediaLibraryFileUpload::make('file')
                ->collection('file')
                ->acceptedFileTypes(fn ($get) => $get('type') === 'video'
                    ? ['video/mp4']
                    : ['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(153600)
                ->visible(fn ($get) => in_array($get('type'), ['image', 'video'], true))
                ->saveRelationshipsWhenHidden()
                ->columnSpanFull(),
            TextInput::make('youtube_url')
                ->label('YouTube URL')
                ->url()
                ->required(fn ($get) => $get('type') === 'youtube')
                ->visible(fn ($get) => $get('type') === 'youtube')
                ->helperText('Any YouTube link — watch, youtu.be, embed or shorts.')
                ->columnSpanFull(),
            TextInput::make('caption')->columnSpanFull(),
            TextInput::make('order')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('caption')
            ->columns([
                TextColumn::make('order')->sortable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('caption')->limit(40),
                TextColumn::make('youtube_url')->limit(40)->toggleable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }
}
