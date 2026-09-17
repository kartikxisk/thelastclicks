<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                // `service_items.company_id` is cascadeOnDelete, so a
                // company-scoped rate card is removed along with the company —
                // silently, and with no way back. Counting the rows in the
                // confirmation is what turns that into a decision rather than a
                // discovery. Shared rows (company_id null) belong to no company
                // and survive, so they are deliberately not counted.
                ->modalDescription(function (Company $record): string {
                    $rateCardRows = $record->serviceItems()->count();

                    return $rateCardRows === 0
                        ? 'This company has no rate-card rows of its own. Are you sure you want to delete it?'
                        : "Deleting this company also deletes {$rateCardRows} rate-card row(s) scoped to it. This cannot be undone.";
                }),
        ];
    }
}
