<?php

use App\Invoicing\StateCodes;

it('holds every live GST state code', function () {
    expect(StateCodes::CODES)->toHaveCount(37)
        ->and(StateCodes::name('07'))->toBe('Delhi')
        ->and(StateCodes::name('27'))->toBe('Maharashtra')
        ->and(StateCodes::name('97'))->toBe('Other Territory');
});

it('omits the codes that were merged away', function () {
    // 25 (Daman and Diu) and 28 (undivided Andhra Pradesh) no longer issue
    // GSTINs. Keeping them in the list lets someone pick a state that cannot
    // appear in a valid counterparty GSTIN, and the mismatch only shows up at
    // filing time.
    expect(StateCodes::exists('25'))->toBeFalse()
        ->and(StateCodes::exists('28'))->toBeFalse()
        ->and(StateCodes::exists('26'))->toBeTrue()
        ->and(StateCodes::exists('37'))->toBeTrue();
});

it('rejects unknown and malformed codes', function () {
    expect(StateCodes::exists('99'))->toBeFalse()
        ->and(StateCodes::exists('7'))->toBeFalse()
        ->and(StateCodes::exists(null))->toBeFalse()
        ->and(StateCodes::name('99'))->toBeNull();
});

it('labels options with the code, because the code is what appears on a GSTIN', function () {
    expect(StateCodes::options()['07'])->toBe('07 — Delhi');
});
