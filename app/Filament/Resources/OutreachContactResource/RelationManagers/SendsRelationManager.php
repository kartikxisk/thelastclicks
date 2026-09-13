<?php

namespace App\Filament\Resources\OutreachContactResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * What actually went to this contact.
 *
 * Read-only on purpose: this is the log the send path checks before mailing, so
 * an editable row here could un-suppress a message that has already left.
 */
class SendsRelationManager extends RelationManager
{
    protected static string $relationship = 'sends';

    protected static ?string $title = 'Send history';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sent_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('sent_at')->dateTime('d M Y, H:i')->label('Sent'),
                Tables\Columns\TextColumn::make('step')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'sent' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('subject')->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('error')->wrap()->toggleable()->color('danger'),
            ])
            ->emptyStateHeading('Nothing sent yet');
    }
}
