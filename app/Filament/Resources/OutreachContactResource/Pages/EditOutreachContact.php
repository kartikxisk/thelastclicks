<?php

namespace App\Filament\Resources\OutreachContactResource\Pages;

use App\Filament\Resources\OutreachContactResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutreachContact extends EditRecord
{
    protected static string $resource = OutreachContactResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
