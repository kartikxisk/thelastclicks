<?php

namespace App\Filament\Resources\BillingClientResource\Pages;

use App\Filament\Resources\BillingClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBillingClients extends ListRecords
{
    protected static string $resource = BillingClientResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
