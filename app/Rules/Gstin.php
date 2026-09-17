<?php

namespace App\Rules;

use App\Invoicing\StateCodes;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A GSTIN is 15 characters with a check digit, and the check digit is the whole
 * point of validating it here.
 *
 * The format regex catches a mangled paste; only the mod-36 checksum catches a
 * single transposed character, which is the error people actually make. A wrong
 * GSTIN on an issued invoice cannot be corrected by editing it — it takes a
 * credit note — so the cheap place to catch it is the form.
 */
class Gstin implements ValidationRule
{
    private const ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private const FORMAT = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[A-Z0-9]$/';

    /** @param  string|null  $stateCode  When set, the GSTIN must belong to this state. */
    public function __construct(private ?string $stateCode = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Blank is not this rule's business — `required` says whether a value
        // must be present, and duplicating that here produces two errors for
        // one empty field.
        if ($value === null || $value === '') {
            return;
        }

        $gstin = (string) $value;

        if (preg_match(self::FORMAT, $gstin) !== 1) {
            $fail('The :attribute must be 15 characters: 2 digits, 5 letters, 4 digits, a letter, one character, Z, then the check digit.');

            return;
        }

        $state = substr($gstin, 0, 2);

        if (! StateCodes::exists($state)) {
            $fail("The :attribute starts with state code {$state}, which no longer issues registrations.");

            return;
        }

        if (self::checksum(substr($gstin, 0, 14)) !== $gstin[14]) {
            $fail('The :attribute failed its checksum — one character is wrong.');

            return;
        }

        if ($this->stateCode !== null && $state !== $this->stateCode) {
            $expected = StateCodes::name($this->stateCode) ?? $this->stateCode;
            $actual = StateCodes::name($state) ?? $state;

            $fail("The :attribute belongs to {$actual}, but the selected state is {$expected}.");
        }
    }

    public static function isValid(?string $gstin): bool
    {
        if ($gstin === null || $gstin === '') {
            return false;
        }

        $failed = false;
        /** @phpstan-ignore-next-line Parameter type mismatch is expected — ValidationRule's fail closure signature is strict but our use case only needs boolean tracking. */
        (new self)->validate('gstin', $gstin, function (string $message) use (&$failed): mixed {
            $failed = true;

            return $message;
        });

        return ! $failed;
    }

    /**
     * The GSTIN check digit: positional weights alternating 1 and 2, each
     * product folded (quotient + remainder in base 36), then complemented.
     *
     * @param  string  $first14  The GSTIN without its check digit.
     */
    public static function checksum(string $first14): string
    {
        $sum = 0;

        for ($i = 0; $i < 14; $i++) {
            $value = strpos(self::ALPHABET, $first14[$i]);

            if ($value === false) {
                return '?';
            }

            $product = $value * ($i % 2 === 0 ? 1 : 2);
            $sum += intdiv($product, 36) + ($product % 36);
        }

        return self::ALPHABET[(36 - ($sum % 36)) % 36];
    }
}
