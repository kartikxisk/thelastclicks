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

    /**
     * @param  Closure(string): mixed  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Blank is not this rule's business — `required` says whether a value
        // must be present, and duplicating that here produces two errors for
        // one empty field.
        if ($value === null || $value === '') {
            return;
        }

        // Case carries no information in a GSTIN — every issued one is
        // uppercase — so a lowercase paste out of an email is normalised here
        // rather than rejected. Form validation runs BEFORE the field's
        // dehydration, so uppercasing only on the way into the column would
        // still leave the admin facing "the format is invalid", which names the
        // wrong problem: the characters are right and only the case is not.
        $gstin = strtoupper(trim((string) $value));

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
        (new self)->validate('gstin', $gstin, function () use (&$failed) {
            $failed = true;
        });

        return ! $failed;
    }

    /**
     * The GSTIN check digit: positional weights alternating 1 and 2, each
     * product folded (quotient + remainder in base 36), then complemented.
     *
     * @param  string  $first14  The GSTIN without its check digit.
     *
     * @throws \InvalidArgumentException
     */
    public static function checksum(string $first14): string
    {
        // Length guard is essential: a short string's missing offsets emit
        // Uninitialized string offset warnings and return '', which strpos
        // interprets as 0 (found at position 0), hiding the corruption.
        // A too-long string is silently truncated. Since factories call this
        // to mint valid GSTINs, a bug here becomes a silently-wrong fixture
        // instead of a loudly-failing test.
        if (strlen($first14) !== 14) {
            throw new \InvalidArgumentException('GSTIN stub must be exactly 14 characters, got '.strlen($first14).": {$first14}");
        }

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
