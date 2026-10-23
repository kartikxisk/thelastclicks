<?php

namespace App\Filament\Resources\ServiceLocationResource\Pages;

use App\Filament\Resources\ServiceLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceLocations extends ListRecords
{
    protected static string $resource = ServiceLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
