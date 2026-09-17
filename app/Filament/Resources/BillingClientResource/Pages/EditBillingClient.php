<?php

namespace App\Filament\Resources\BillingClientResource\Pages;

use App\Filament\Resources\BillingClientResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBillingClient extends EditRecord
{
    protected static string $resource = BillingClientResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
