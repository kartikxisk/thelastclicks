<?php

namespace App\Invoicing;

use InvalidArgumentException;

/**
 * Rupees are never a float in this codebase.
 *
 * Every monetary column is a bigint of paise and this class is the only place
 * that converts. The reason is the three-way GST split: ₹1,234.55 at 18%
 * intra-state produces two components of ₹111.1095 each, and a float round-trip
 * (MySQL returns `decimal` as a string, PHP then does the arithmetic in binary
 * floating point) leaves the components and the grand total a paisa apart. That
 * paisa surfaces months later in a GSTR-1 reconciliation, on a document the law
 * no longer lets us edit.
 *
 * It is not an Eloquent cast on purpose. A cast that handed models rupees would
 * put the float back exactly where it was removed from.
 */
final class Money
{
    /** @var array<string, string> */
    private const SYMBOLS = ['INR' => '₹'];

    private const ONES = [
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
        15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen',
    ];

    private const TENS = [
        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety',
    ];

    /**
     * Parse a human-entered amount in rupees into paise.
     *
     * Accepts what people actually paste: grouping commas, a rupee sign,
     * stray spaces. A third decimal rounds half-up rather than truncating,
     * because truncation always rounds in our favour and that is the direction
     * a client notices.
     *
     * A float is deliberately not accepted. `(string) 1.0e20` is "1.0E+20", and
     * the strip below removes the "E+" rather than the exponent it stands for,
     * leaving "1.020" — 102 paise, returned silently. Refusing the type keeps
     * the one class whose whole purpose is that money never becomes a float
     * from being handed one; PHPStan is what enforces it, since PHP itself
     * would quietly coerce the argument to a string here.
     */
    public static function fromRupees(string|int|null $input): int
    {
        if ($input === null || $input === '') {
            return 0;
        }

        $raw = trim((string) $input);
        $negative = str_starts_with($raw, '-');

        // The same exponent shape still arrives as a string — out of a CSV
        // import, a JSON payload, or a float someone cast before calling. The
        // strip below cannot represent it, so it must be refused rather than
        // silently misread as the digits that survive.
        if (preg_match('/[eE]/', $raw) === 1) {
            throw new InvalidArgumentException("Not an amount: {$raw}");
        }

        $digits = preg_replace('/[^0-9.]/', '', $raw) ?? '';

        if ($digits === '' || substr_count($digits, '.') > 1) {
            throw new InvalidArgumentException("Not an amount: {$raw}");
        }

        [$whole, $fraction] = array_pad(explode('.', $digits, 2), 2, '');

        $fraction = str_pad(substr($fraction, 0, 3), 3, '0');
        $paise = ((int) ($whole === '' ? '0' : $whole)) * 100 + (int) substr($fraction, 0, 2);

        if ((int) $fraction[2] >= 5) {
            $paise++;
        }

        return $negative ? -$paise : $paise;
    }

    /** Paise as a plain `1234.55` string — what a form field holds. */
    public static function toRupees(int $paise): string
    {
        $abs = abs($paise);

        return ($paise < 0 ? '-' : '')
            .intdiv($abs, 100)
            .'.'
            .str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT);
    }

    /** Display form: `₹1,23,456.00`, with Indian grouping for INR. */
    public static function format(int $paise, string $currency = 'INR'): string
    {
        $abs = abs($paise);
        $rupees = (string) intdiv($abs, 100);
        $decimals = str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT);

        $grouped = $currency === 'INR'
            ? self::groupIndian($rupees)
            : strrev(implode(',', str_split(strrev($rupees), 3)));

        $prefix = self::SYMBOLS[$currency] ?? $currency.' ';

        return ($paise < 0 ? '-' : '').$prefix.$grouped.'.'.$decimals;
    }

    /**
     * Apply a basis-point rate, rounding a half AWAY FROM ZERO at the paise.
     *
     * Not half-up: applyBps(-123455, 1800) is -22222, where half-up would give
     * -22221. Symmetric rounding is what credit notes need — a credit note is
     * this arithmetic with the sign flipped, and only away-from-zero makes
     * credit(x) === -invoice(x). Under half-up the two miss each other by a
     * paisa on the one pair of documents whose whole purpose is to net to
     * nothing. Spec section 5 said "round_half_up" for a while; the spec was
     * wrong and has been corrected to match this.
     *
     * Basis points, not percentages, so a 2.5% half-rate is the integer 250 and
     * not a float that cannot represent itself.
     */
    public static function applyBps(int $paise, int $bps): int
    {
        $product = $paise * $bps;
        $sign = $product < 0 ? -1 : 1;

        return $sign * intdiv(abs($product) + 5000, 10000);
    }

    /**
     * Round to the nearest rupee, a half going AWAY FROM ZERO — CGST §170.
     *
     * roundToRupee(-12350) is -12400, not -12300. Same reason as applyBps():
     * a credit note must cancel the invoice it reverses exactly.
     *
     * Only ever applied to an invoice grand total. Applying it to a tax
     * component instead is what makes a return disagree with the ledger.
     */
    public static function roundToRupee(int $paise): int
    {
        $sign = $paise < 0 ? -1 : 1;
        $abs = abs($paise);
        $remainder = $abs % 100;

        return $sign * ($remainder >= 50 ? $abs + (100 - $remainder) : $abs - $remainder);
    }

    /**
     * The total in words, Indian numbering.
     *
     * Not a Rule 46 requirement, but every accounts-payable department expects
     * it and a few refuse an invoice without it. The sign must be preserved on
     * legal documents: a credit note or round-off amount can be negative, and
     * an unsigned words method would produce an invoice with a wrong total.
     */
    public static function inWords(int $paise): string
    {
        $negative = $paise < 0;
        $abs = abs($paise);
        $rupees = intdiv($abs, 100);
        $fraction = $abs % 100;

        $words = 'Rupees '.($rupees === 0 ? 'Zero' : self::integerInWords($rupees));

        if ($fraction > 0) {
            $words .= ' and '.self::integerInWords($fraction).' Paise';
        }

        return ($negative ? 'Minus ' : '').$words.' Only';
    }

    /** Last three digits, then pairs — 12345600 paise reads as 1,23,456. */
    private static function groupIndian(string $rupees): string
    {
        if (strlen($rupees) <= 3) {
            return $rupees;
        }

        $last3 = substr($rupees, -3);
        $rest = substr($rupees, 0, -3);

        return strrev(implode(',', str_split(strrev($rest), 2))).','.$last3;
    }

    private static function integerInWords(int $n): string
    {
        $parts = [];

        foreach ([10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand', 100 => 'Hundred'] as $unit => $label) {
            $count = intdiv($n, $unit);

            if ($count > 0) {
                $parts[] = self::integerInWords($count).' '.$label;
                $n %= $unit;
            }
        }

        if ($n > 0) {
            $parts[] = $n < 20
                ? self::ONES[$n]
                : trim(self::TENS[intdiv($n, 10)].' '.self::ONES[$n % 10]);
        }

        return implode(' ', $parts);
    }
}
