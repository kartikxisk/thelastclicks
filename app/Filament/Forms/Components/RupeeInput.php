<?php

namespace App\Filament\Forms\Components;

use App\Invoicing\Money;
use Filament\Forms\Components\TextInput;

/**
 * A money field: rupees on screen, paise in the column.
 *
 * Every monetary column is an integer of paise, so the conversion has to happen
 * somewhere. Doing it here means no form can forget, and no Eloquent cast has
 * to hand a float back to the tax engine.
 */
class RupeeInput
{
    public static function make(string $name): TextInput
    {
        return TextInput::make($name)
            ->prefix('₹')
            // At least one digit before the optional decimal part is
            // required: a bare '-' used to match this, pass validation, and
            // reach Money::fromRupees('-'), which throws — an admin would see
            // a 500 instead of a field error.
            ->rule('regex:/^-?[0-9][0-9,]*\.?[0-9]{0,2}$/')
            ->default(0)
            ->formatStateUsing(fn (int|string|null $state): string => Money::toRupees((int) ($state ?? 0)))
            ->dehydrateStateUsing(fn (int|string|null $state): int => Money::fromRupees($state));
    }
}
