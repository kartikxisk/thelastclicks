<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    /**
     * Put the bank block back, because $hidden takes it out.
     *
     * Company::$hidden keeps bank_account_number, bank_ifsc and upi_id out of
     * toArray(), and Filament fills this form from $record->attributesToArray()
     * — so without this the edit screen comes up with three blank fields and
     * saving writes those blanks straight over real bank details. Reaching for
     * the attributes explicitly is the intended way past $hidden; phase 2's
     * party_snapshot has to do the same rather than relying on toArray().
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (['bank_account_number', 'bank_ifsc', 'upi_id'] as $attribute) {
            $data[$attribute] = $this->getRecord()->getAttribute($attribute);
        }

        return $data;
    }

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
