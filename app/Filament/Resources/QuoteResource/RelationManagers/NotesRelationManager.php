<?php

namespace App\Filament\Resources\QuoteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('body')->required()->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                Tables\Columns\TextColumn::make('author.name')->label('By'),
                Tables\Columns\TextColumn::make('body')->wrap()->limit(120),
                Tables\Columns\TextColumn::make('stage')
                    ->label('Stage')
                    ->badge()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->since(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    // Stamp the stage the lead is at right now; it must not follow
                    // the lead as it moves on.
                    ->mutateFormDataUsing(fn (array $data) => [
                        ...$data,
                        'author_id' => auth()->id(),
                        'stage' => $this->getOwnerRecord()->status,
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
