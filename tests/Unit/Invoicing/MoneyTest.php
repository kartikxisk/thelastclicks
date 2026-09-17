<?php

use App\Invoicing\Money;

it('parses rupee input into paise without touching a float', function () {
    expect(Money::fromRupees('1234.55'))->toBe(123455)
        ->and(Money::fromRupees('1234.5'))->toBe(123450)
        ->and(Money::fromRupees('1234'))->toBe(123400)
        ->and(Money::fromRupees('1,23,456'))->toBe(12345600)
        ->and(Money::fromRupees('₹ 1,234.55'))->toBe(123455)
        ->and(Money::fromRupees(''))->toBe(0)
        ->and(Money::fromRupees(null))->toBe(0)
        ->and(Money::fromRupees('-1234.55'))->toBe(-123455)
        ->and(Money::fromRupees('-1,23,456'))->toBe(-12345600);
});

it('rounds a third decimal half-up rather than truncating it', function () {
    // A pasted "1234.555" must not silently become ₹1234.55 — the half-paisa
    // has to go somewhere, and truncation always loses in our favour, which is
    // the direction that gets noticed.
    expect(Money::fromRupees('1234.555'))->toBe(123456)
        ->and(Money::fromRupees('1234.554'))->toBe(123455);
});

it('round-trips paise back to a form-shaped rupee string', function () {
    expect(Money::toRupees(123455))->toBe('1234.55')
        ->and(Money::toRupees(123400))->toBe('1234.00')
        ->and(Money::toRupees(5))->toBe('0.05')
        ->and(Money::toRupees(-123455))->toBe('-1234.55');
});

it('formats with Indian digit grouping', function () {
    expect(Money::format(12345600))->toBe('₹1,23,456.00')
        ->and(Money::format(100000000))->toBe('₹10,00,000.00')
        ->and(Money::format(99900))->toBe('₹999.00')
        ->and(Money::format(123455, 'USD'))->toBe('USD 1,234.55')
        ->and(Money::format(-123455))->toBe('-₹1,234.55')
        ->and(Money::format(-12345600))->toBe('-₹1,23,456.00');
});

it('applies a basis-point rate half-up', function () {
    // ₹1,234.55 at 18% = ₹222.219 → ₹222.22
    expect(Money::applyBps(123455, 1800))->toBe(22222)
        // The same value at 9% (one half of an intra-state split) = ₹111.1095 → ₹111.11
        ->and(Money::applyBps(123455, 900))->toBe(11111)
        ->and(Money::applyBps(0, 1800))->toBe(0)
        ->and(Money::applyBps(-123455, 1800))->toBe(-22222)
        ->and(Money::applyBps(-123455, 900))->toBe(-11111);
});

it('rounds a total to the nearest rupee, half-up, per section 170', function () {
    expect(Money::roundToRupee(12350))->toBe(12400)
        ->and(Money::roundToRupee(12349))->toBe(12300)
        ->and(Money::roundToRupee(12300))->toBe(12300)
        ->and(Money::roundToRupee(-12350))->toBe(-12400);
});

it('writes the total in words the Indian way', function () {
    expect(Money::inWords(12345600))->toBe('Rupees One Lakh Twenty Three Thousand Four Hundred Fifty Six Only')
        ->and(Money::inWords(100000000))->toBe('Rupees Ten Lakh Only')
        ->and(Money::inWords(1000000000))->toBe('Rupees One Crore Only')
        ->and(Money::inWords(123455))->toBe('Rupees One Thousand Two Hundred Thirty Four and Fifty Five Paise Only')
        ->and(Money::inWords(0))->toBe('Rupees Zero Only')
        ->and(Money::inWords(-12345600))->toBe('Minus Rupees One Lakh Twenty Three Thousand Four Hundred Fifty Six Only')
        ->and(Money::inWords(-123455))->toBe('Minus Rupees One Thousand Two Hundred Thirty Four and Fifty Five Paise Only');
});

it('refuses an amount in scientific notation rather than silently misreading it', function () {
    // (string) 1.0e20 is "1.0E+20". Stripping everything but digits and the dot
    // left "1.020", which parsed as 102 paise — a wrong number, returned
    // silently, from the one class whose whole purpose is that this cannot
    // happen. The float overload that made this reachable by accident is gone
    // from the signature, but a string in this shape still arrives from a CSV
    // or a JSON payload, so it has to be refused rather than guessed at.
    expect(fn () => Money::fromRupees('1.0E+20'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => Money::fromRupees('1e3'))->toThrow(InvalidArgumentException::class);
});
