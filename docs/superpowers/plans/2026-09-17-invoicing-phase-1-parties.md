# Invoicing Phase 1 — Parties Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the three party tables invoicing stands on — our companies, the clients we bill, and the saved rate card — plus the money, state-code and GSTIN primitives every later phase depends on.

**Architecture:** Three Eloquent models with Filament resources under a new **Billing** navigation group, sitting on top of four dependency-free support classes (`Money`, `StateCodes`, `Gstin`, `RupeeInput`). Money is stored as integer paise everywhere; rupees exist only at the form boundary. One company is always the default, enforced by an observer rather than by hope.

**Tech Stack:** Laravel 11 (PHP 8.4 via `./bin/php`), Filament 3, filament-shield, spatie/laravel-medialibrary, Pest 3, SQLite in tests / MySQL in production.

**Spec:** `docs/superpowers/specs/2026-09-17-invoicing-design.md`

## Global Constraints

- **PHP is `./bin/php`.** A bare `php` on this machine may be the wrong version. Composer platform is pinned to 8.4.0.
- **Money is integer paise in `bigint` columns suffixed `_paise`.** No floats, no `decimal`. Tax rates are integer basis points (`18%` → `1800`).
- **No `git commit` steps.** Project preference: work stays in the VSCode changes panel and the user commits when they choose. Each task ends at a green test run instead.
- **CI runs exactly three commands, in this order:** `./vendor/bin/pint --test`, `./vendor/bin/phpstan analyse --memory-limit=512M --no-progress` (larastan level 6, `app/` only), `./vendor/bin/pest --no-coverage`. Larastan level 6 means every relation and builder needs its generic docblock — see the code below for the exact shapes.
- **Tests:** Pest. `uses(RefreshDatabase::class)` per file, `beforeEach(fn () => $this->seed())` where seeded fixtures are needed. Assert on structure, not copy.
- **Comments explain *why*,** naming the failure the code prevents. A comment that restates the line is noise.
- **Shield permission suffixes** come from the resource name, not the model: `Company` → `company`, `BillingClient` → `billing::client`, `ServiceItem` → `service::item`.
- **Deviation from the spec, deliberate:** `Money` lands in phase 1, not phase 2, because `service_items.rate_paise` needs it. It is a static utility, **not** an Eloquent cast — a cast returning rupees would reintroduce the float the integer columns exist to avoid.

---

### Task 1: Money

**Files:**
- Create: `app/Invoicing/Money.php`
- Test: `tests/Unit/Invoicing/MoneyTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces:
  - `Money::fromRupees(string|int|float|null $input): int` — human input → paise
  - `Money::toRupees(int $paise): string` — `"1234.55"`, for form fields
  - `Money::format(int $paise, string $currency = 'INR'): string` — `"₹1,23,456.00"`
  - `Money::applyBps(int $paise, int $bps): int` — percentage rounded a half away from zero, for the phase-2 tax engine
  - `Money::roundToRupee(int $paise): int` — §170 rounding
  - `Money::inWords(int $paise): string` — `"Rupees … Only"`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Invoicing/MoneyTest.php`:

```php
<?php

use App\Invoicing\Money;

it('parses rupee input into paise without touching a float', function () {
    expect(Money::fromRupees('1234.55'))->toBe(123455)
        ->and(Money::fromRupees('1234.5'))->toBe(123450)
        ->and(Money::fromRupees('1234'))->toBe(123400)
        ->and(Money::fromRupees('1,23,456'))->toBe(12345600)
        ->and(Money::fromRupees('₹ 1,234.55'))->toBe(123455)
        ->and(Money::fromRupees(''))->toBe(0)
        ->and(Money::fromRupees(null))->toBe(0);
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
        ->and(Money::format(123455, 'USD'))->toBe('USD 1,234.55');
});

it('applies a basis-point rate half-up', function () {
    // ₹1,234.55 at 18% = ₹222.219 → ₹222.22
    expect(Money::applyBps(123455, 1800))->toBe(22222)
        // The same value at 9% (one half of an intra-state split) = ₹111.1095 → ₹111.11
        ->and(Money::applyBps(123455, 900))->toBe(11111)
        ->and(Money::applyBps(0, 1800))->toBe(0);
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
        ->and(Money::inWords(0))->toBe('Rupees Zero Only');
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Unit/Invoicing/MoneyTest.php`
Expected: FAIL — `Class "App\Invoicing\Money" not found`.

- [ ] **Step 3: Implement Money**

Create `app/Invoicing/Money.php`:

```php
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
     */
    public static function fromRupees(string|int|float|null $input): int
    {
        if ($input === null || $input === '') {
            return 0;
        }

        $raw = trim((string) $input);
        $negative = str_starts_with($raw, '-');
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
     * credit(x) === -invoice(x).
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
```

- [ ] **Step 4: Run the test and watch it pass**

Run: `./bin/php vendor/bin/pest tests/Unit/Invoicing/MoneyTest.php`
Expected: PASS, 7 tests.

- [ ] **Step 5: Lint and analyse**

Run: `./vendor/bin/pint app/Invoicing tests/Unit && ./vendor/bin/phpstan analyse --memory-limit=512M --no-progress`
Expected: no style diff, no phpstan errors.

---

### Task 2: State codes

**Files:**
- Create: `app/Invoicing/StateCodes.php`
- Test: `tests/Unit/Invoicing/StateCodesTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces:
  - `StateCodes::CODES` — `array<string, string>` keyed by the two-digit code
  - `StateCodes::name(?string $code): ?string`
  - `StateCodes::exists(?string $code): bool`
  - `StateCodes::options(): array<string, string>` — `"07" => "07 — Delhi"`, for Filament selects

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Invoicing/StateCodesTest.php`:

```php
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
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Unit/Invoicing/StateCodesTest.php`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement StateCodes**

Create `app/Invoicing/StateCodes.php`:

```php
<?php

namespace App\Invoicing;

/**
 * GST state codes — the first two digits of every GSTIN, and the input to the
 * intra-state vs inter-state decision.
 *
 * Two legacy codes are deliberately absent: 25 (Daman and Diu, merged into 26
 * in 2020) and 28 (undivided Andhra Pradesh, split into 37 and 36). Neither can
 * appear in a GSTIN issued today, so offering them only lets someone pick a
 * state that will never match the counterparty's registration.
 */
final class StateCodes
{
    /** @var array<string, string> */
    public const CODES = [
        '01' => 'Jammu and Kashmir',
        '02' => 'Himachal Pradesh',
        '03' => 'Punjab',
        '04' => 'Chandigarh',
        '05' => 'Uttarakhand',
        '06' => 'Haryana',
        '07' => 'Delhi',
        '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',
        '10' => 'Bihar',
        '11' => 'Sikkim',
        '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',
        '14' => 'Manipur',
        '15' => 'Mizoram',
        '16' => 'Tripura',
        '17' => 'Meghalaya',
        '18' => 'Assam',
        '19' => 'West Bengal',
        '20' => 'Jharkhand',
        '21' => 'Odisha',
        '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',
        '24' => 'Gujarat',
        '26' => 'Dadra and Nagar Haveli and Daman and Diu',
        '27' => 'Maharashtra',
        '29' => 'Karnataka',
        '30' => 'Goa',
        '31' => 'Lakshadweep',
        '32' => 'Kerala',
        '33' => 'Tamil Nadu',
        '34' => 'Puducherry',
        '35' => 'Andaman and Nicobar Islands',
        '36' => 'Telangana',
        '37' => 'Andhra Pradesh',
        '38' => 'Ladakh',
        '97' => 'Other Territory',
    ];

    public static function name(?string $code): ?string
    {
        return $code === null ? null : (self::CODES[$code] ?? null);
    }

    public static function exists(?string $code): bool
    {
        return $code !== null && array_key_exists($code, self::CODES);
    }

    /**
     * Select options. The code is part of the label because it is what a client
     * reads off their own GSTIN when checking we billed them correctly.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::CODES as $code => $name) {
            $options[$code] = $code.' — '.$name;
        }

        return $options;
    }
}
```

- [ ] **Step 4: Run the test and watch it pass**

Run: `./bin/php vendor/bin/pest tests/Unit/Invoicing/StateCodesTest.php`
Expected: PASS, 4 tests.

---

### Task 3: GSTIN validation

**Files:**
- Create: `app/Rules/Gstin.php`
- Test: `tests/Unit/Invoicing/GstinTest.php`

**Interfaces:**
- Consumes: `App\Invoicing\StateCodes`
- Produces:
  - `new Gstin()` — a `ValidationRule` for form fields
  - `new Gstin(stateCode: '07')` — additionally requires the GSTIN to belong to that state
  - `Gstin::isValid(?string $gstin): bool`
  - `Gstin::checksum(string $first14): string` — used by the factories to mint valid fixtures

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Invoicing/GstinTest.php`:

```php
<?php

use App\Rules\Gstin;

/** Collect the failure message a rule emits, or null when it passes. */
function gstinFailure(string $value, ?string $stateCode = null): ?string
{
    $message = null;
    (new Gstin($stateCode))->validate('gstin', $value, function (string $m) use (&$message) {
        $message = $m;
    });

    return $message;
}

it('accepts published, checksum-valid GSTINs', function () {
    // Both verified against the mod-36 algorithm rather than trusted from a
    // blog: a fixture with a bad checksum would make the rule look correct
    // while rejecting every real registration.
    expect(gstinFailure('27AAPFU0939F1ZV'))->toBeNull()
        ->and(gstinFailure('24AAACC1206D1ZM'))->toBeNull()
        ->and(Gstin::isValid('27AAPFU0939F1ZV'))->toBeTrue();
});

it('rejects a GSTIN whose checksum does not match', function () {
    // Correct shape, wrong last character — the exact shape of a typo, and the
    // only class of error the format regex cannot see.
    expect(gstinFailure('07AAACT2727Q1ZW'))->toContain('checksum');
});

it('rejects the wrong shape', function () {
    expect(gstinFailure('27AAPFU0939F1Z'))->not->toBeNull()        // 14 chars
        ->and(gstinFailure('27aapfu0939f1zv'))->not->toBeNull()     // lowercase
        ->and(gstinFailure('27AAPFU0939F1AV'))->not->toBeNull()     // 14th char is not Z
        ->and(gstinFailure('AAAAAAAAAAAAAAA'))->not->toBeNull();
});

it('rejects a state code that no longer issues registrations', function () {
    expect(gstinFailure('99AAPFU0939F1ZV'))->toContain('state code');
});

it('can require the GSTIN to belong to a given state', function () {
    expect(gstinFailure('27AAPFU0939F1ZV', '27'))->toBeNull()
        ->and(gstinFailure('27AAPFU0939F1ZV', '07'))->toContain('Delhi');
});

it('exposes the checksum so fixtures can mint valid GSTINs', function () {
    expect(Gstin::checksum('27AAPFU0939F1Z'))->toBe('V')
        ->and(Gstin::checksum('24AAACC1206D1Z'))->toBe('M');
});

it('treats an empty value as absent, leaving `required` to decide', function () {
    expect(gstinFailure(''))->toBeNull()
        ->and(Gstin::isValid(null))->toBeFalse();
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Unit/Invoicing/GstinTest.php`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement the rule**

Create `app/Rules/Gstin.php`:

```php
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
```

- [ ] **Step 4: Run the test and watch it pass**

Run: `./bin/php vendor/bin/pest tests/Unit/Invoicing/GstinTest.php`
Expected: PASS, 7 tests.

- [ ] **Step 5: Run the whole unit suite and the linters**

Run: `./bin/php vendor/bin/pest tests/Unit && ./vendor/bin/pint app tests && ./vendor/bin/phpstan analyse --memory-limit=512M --no-progress`
Expected: all pass.

---

### Task 4: Companies table and model

**Files:**
- Create: `database/migrations/2026_09_17_000001_create_companies_table.php`
- Create: `app/Models/Company.php`
- Create: `database/factories/CompanyFactory.php`
- Test: `tests/Feature/Invoicing/CompanyTest.php`

**Interfaces:**
- Consumes: `App\Invoicing\StateCodes`, `App\Rules\Gstin`
- Produces:
  - `Company` model with media collections `logo`, `signature`, `stamp`
  - `Company::default(): ?Company`
  - `$company->addressLines(): list<string>`
  - `$company->stateLabel(): ?string`
  - `Company::scopeActive(Builder $q): void`
  - `CompanyFactory`, GST-registered by default (`definition()` already returns a valid GSTIN — there is no `gstRegistered()` state), with an `unregistered()` state and an `inState(string $stateCode)` state

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Invoicing/CompanyTest.php`:

```php
<?php

use App\Models\Company;
use App\Rules\Gstin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mints fixtures whose GSTIN would survive the real rule', function () {
    // The factory builds a GSTIN by computing its own checksum. If that ever
    // drifts from the rule, every downstream test would pass against a GSTIN no
    // client could actually have.
    $company = Company::factory()->inState('27')->create();

    expect(Gstin::isValid($company->gstin))->toBeTrue()
        ->and(substr($company->gstin, 0, 2))->toBe('27');
});

it('drops blank lines out of the printed address', function () {
    $company = Company::factory()->create([
        'address_line1' => 'B-12, Lajpat Nagar',
        'address_line2' => null,
        'address_city' => 'New Delhi',
        'address_state' => 'Delhi',
        'address_postal_code' => '110024',
    ]);

    expect($company->addressLines())->toBe([
        'B-12, Lajpat Nagar',
        'New Delhi, Delhi 110024',
    ]);
});

it('labels its state from the code', function () {
    $company = Company::factory()->create(['address_state_code' => '07']);

    expect($company->stateLabel())->toBe('Delhi');
});

it('scopes to active companies', function () {
    Company::factory()->create(['is_active' => true]);
    Company::factory()->create(['is_active' => false]);

    expect(Company::active()->count())->toBe(1);
});

it('registers the three branding collections an invoice needs', function () {
    $company = Company::factory()->create();

    expect(collect($company->getRegisteredMediaCollections())->pluck('name')->all())
        ->toBe(['logo', 'signature', 'stamp']);
});

it('can be unregistered, in which case it carries no GSTIN', function () {
    $company = Company::factory()->unregistered()->create();

    expect($company->is_gst_registered)->toBeFalse()
        ->and($company->gstin)->toBeNull();
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Feature/Invoicing/CompanyTest.php`
Expected: FAIL — `Class "App\Models\Company" not found`.

- [ ] **Step 3: Write the migration**

Create `database/migrations/2026_09_17_000001_create_companies_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Our own billing entities.
 *
 * Every field here is either printed on an invoice or decides how one is
 * calculated — `address_state_code` in particular is the input to the
 * intra-state vs inter-state tax split, which is why it is a separate column
 * and not something parsed back out of the address.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();

            $table->boolean('is_gst_registered')->default(true);
            $table->string('gstin', 15)->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('cin', 21)->nullable();
            $table->string('lut_number')->nullable();
            $table->date('lut_valid_till')->nullable();

            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state')->nullable();
            $table->string('address_state_code', 2)->nullable();
            $table->string('address_postal_code', 6)->nullable();
            $table->string('address_country', 2)->default('IN');

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc', 11)->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('upi_id')->nullable();

            // Capped at 5 because the longest number this produces —
            // PREFX/26-27/0001 — is exactly the 16 characters Rule 46(b) allows.
            $table->string('invoice_prefix', 5)->default('INV');
            $table->string('credit_note_prefix', 5)->default('CRN');
            $table->string('proforma_prefix', 5)->default('PRO');
            $table->string('receipt_prefix', 5)->default('RCT');

            $table->string('default_template')->default('classic');
            $table->string('default_currency', 3)->default('INR');
            $table->unsignedInteger('default_payment_terms_days')->default(7);
            $table->text('default_terms')->nullable();
            $table->text('default_notes')->nullable();
            $table->text('footer_note')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
```

- [ ] **Step 4: Write the model**

Create `app/Models/Company.php`:

```php
<?php

namespace App\Models;

use App\Invoicing\StateCodes;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * One of our legal entities. Exactly one is the default, which is what a new
 * invoice prefills from — see CompanyObserver for how that invariant is held.
 */
class Company extends Model implements HasMedia
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, InteractsWithMedia;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_gst_registered' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'lut_valid_till' => 'date',
            'default_payment_terms_days' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        // All three print on the PDF and all three are one-per-company: a second
        // signature file would render whichever medialibrary returned first.
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('signature')->singleFile();
        $this->addMediaCollection('stamp')->singleFile();
    }

    /** @param  Builder<Company>  $q */
    public function scopeActive(Builder $q): void
    {
        $q->where('is_active', true);
    }

    /** The entity a new invoice starts from. */
    public static function default(): ?self
    {
        return self::query()->where('is_default', true)->where('is_active', true)->first();
    }

    public function stateLabel(): ?string
    {
        return StateCodes::name($this->address_state_code);
    }

    /**
     * The address as printed lines, blanks removed.
     *
     * @return list<string>
     */
    public function addressLines(): array
    {
        $cityLine = trim(implode(', ', array_filter([$this->address_city, $this->address_state])));

        if ($cityLine !== '' && filled($this->address_postal_code)) {
            $cityLine .= ' '.$this->address_postal_code;
        }

        return array_values(array_filter([
            $this->address_line1,
            $this->address_line2,
            $cityLine === '' ? null : $cityLine,
        ], fn (?string $line): bool => filled($line)));
    }
}
```

- [ ] **Step 5: Write the factory**

Create `database/factories/CompanyFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Invoicing\StateCodes;
use App\Models\Company;
use App\Rules\Gstin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $stateCode = '07';

        return [
            'name' => fake()->company(),
            'legal_name' => fake()->company().' Private Limited',
            'is_gst_registered' => true,
            'gstin' => self::gstinFor($stateCode),
            'pan' => 'AAPFU0939F',
            'address_line1' => fake()->streetAddress(),
            'address_city' => 'New Delhi',
            'address_state' => StateCodes::name($stateCode),
            'address_state_code' => $stateCode,
            'address_postal_code' => '110024',
            'address_country' => 'IN',
            'email' => fake()->companyEmail(),
            'phone' => '+91 98100 00000',
            'bank_name' => 'HDFC Bank',
            'bank_account_name' => fake()->company(),
            'bank_account_number' => (string) fake()->numberBetween(10000000000, 99999999999),
            'bank_ifsc' => 'HDFC0001234',
            'upi_id' => 'studio@hdfcbank',
            'invoice_prefix' => 'TLC',
            'credit_note_prefix' => 'TLCC',
            'proforma_prefix' => 'TLCP',
            'receipt_prefix' => 'TLCR',
            'is_default' => false,
            'is_active' => true,
        ];
    }

    public function unregistered(): self
    {
        return $this->state(fn (): array => [
            'is_gst_registered' => false,
            'gstin' => null,
        ]);
    }

    /**
     * Build a GSTIN that passes the real rule, by computing the same check digit
     * the rule verifies. A hardcoded fixture would either be one real
     * registration repeated everywhere or, worse, a made-up string that only
     * passes because the rule is broken.
     */
    public static function gstinFor(string $stateCode): string
    {
        $first14 = $stateCode.'AAPFU0939F1Z';

        return $first14.Gstin::checksum($first14);
    }

    /** Keep the GSTIN's state in step when a test picks a different one. */
    public function inState(string $stateCode): self
    {
        return $this->state(fn (): array => [
            'address_state_code' => $stateCode,
            'address_state' => StateCodes::name($stateCode),
            'gstin' => self::gstinFor($stateCode),
        ]);
    }
}
```

- [ ] **Step 6: Run the migration and the test**

Run: `./bin/php vendor/bin/pest tests/Feature/Invoicing/CompanyTest.php`
Expected: PASS, 6 tests. (`RefreshDatabase` runs the migration; no manual `migrate` needed.)

---

### Task 5: The default-company invariant

**Files:**
- Create: `app/Observers/CompanyObserver.php`
- Modify: `app/Providers/AppServiceProvider.php` — register the observer in `boot()`
- Modify: `app/Models/Company.php` — add `makeDefault()`
- Test: `tests/Feature/Invoicing/CompanyDefaultTest.php`

**Interfaces:**
- Consumes: `App\Models\Company`
- Produces:
  - `$company->makeDefault(): void`
  - `CompanyObserver` — auto-promotes the first company, keeps `is_default` unique, blocks deactivating the default, promotes a successor on delete

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Invoicing/CompanyDefaultTest.php`:

```php
<?php

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('makes the first company the default without being asked', function () {
    // An admin who creates one company and goes straight to raising an invoice
    // must not meet "no default company" — there is only one, so the answer is
    // never ambiguous.
    $first = Company::factory()->create(['is_default' => false]);

    expect($first->fresh()->is_default)->toBeTrue();
});

it('keeps exactly one default', function () {
    $first = Company::factory()->create();
    $second = Company::factory()->create();

    $second->makeDefault();

    expect($second->fresh()->is_default)->toBeTrue()
        ->and($first->fresh()->is_default)->toBeFalse()
        ->and(Company::where('is_default', true)->count())->toBe(1);
});

it('refuses to deactivate the default company', function () {
    // Deactivating it would leave Company::default() null and every invoice form
    // with an empty company field — a state the admin cannot see the cause of.
    $default = Company::factory()->create();
    Company::factory()->create();

    expect(fn () => $default->update(['is_active' => false]))
        ->toThrow(RuntimeException::class, 'default company');
});

it('promotes a successor when the default is deleted', function () {
    $default = Company::factory()->create();
    $other = Company::factory()->create();

    $default->delete();

    expect($other->fresh()->is_default)->toBeTrue();
});

it('lets the last company be deleted without promoting a ghost', function () {
    $only = Company::factory()->create();

    $only->delete();

    expect(Company::count())->toBe(0)
        ->and(Company::default())->toBeNull();
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Feature/Invoicing/CompanyDefaultTest.php`
Expected: FAIL — the first company is not default; `makeDefault()` undefined.

- [ ] **Step 3: Add `makeDefault()` to the model**

In `app/Models/Company.php`, below `default()`:

```php
    /**
     * Promote this company and demote the rest, in one transaction.
     *
     * MySQL has no partial unique index, so "exactly one default" cannot be a
     * constraint — it has to be a code path, and this is the only one.
     */
    public function makeDefault(): void
    {
        DB::transaction(function (): void {
            static::query()->where('id', '!=', $this->id)->update(['is_default' => false]);
            $this->forceFill(['is_default' => true, 'is_active' => true])->save();
        });
    }
```

Add `use Illuminate\Support\Facades\DB;` to the imports.

- [ ] **Step 4: Write the observer**

Create `app/Observers/CompanyObserver.php`:

```php
<?php

namespace App\Observers;

use App\Models\Company;
use RuntimeException;

/**
 * Holds the "exactly one active default" invariant.
 *
 * Every invoice form prefills from Company::default(). If that returns null the
 * form comes up with an empty company field and no explanation, so the ways of
 * reaching that state are closed here rather than being discovered later.
 */
class CompanyObserver
{
    public function creating(Company $company): void
    {
        // The first company is the only one it can be, so don't make someone
        // press a button to say so.
        if (Company::count() === 0) {
            $company->is_default = true;
        }
    }

    public function updating(Company $company): void
    {
        if ($company->is_default && $company->isDirty('is_active') && ! $company->is_active) {
            throw new RuntimeException(
                'Cannot deactivate the default company. Make another company the default first.'
            );
        }
    }

    public function saved(Company $company): void
    {
        if ($company->wasChanged('is_default') && $company->is_default) {
            Company::query()->where('id', '!=', $company->id)->update(['is_default' => false]);
        }
    }

    public function deleted(Company $company): void
    {
        if (! $company->is_default) {
            return;
        }

        // Oldest surviving company wins — arbitrary, but deterministic, and the
        // admin can change it in one click.
        Company::query()->where('is_active', true)->oldest('id')->first()?->makeDefault();
    }
}
```

- [ ] **Step 5: Register the observer**

In `app/Providers/AppServiceProvider.php`, add `use App\Models\Company;` and `use App\Observers\CompanyObserver;`, then inside `boot()` — next to the other `::observe()` calls, before the response-cache block:

```php
        Company::observe(CompanyObserver::class);
```

- [ ] **Step 6: Run the test and watch it pass**

Run: `./bin/php vendor/bin/pest tests/Feature/Invoicing/CompanyDefaultTest.php tests/Feature/Invoicing/CompanyTest.php`
Expected: PASS, 11 tests.

---

### Task 6: Billing clients

**Files:**
- Create: `database/migrations/2026_09_17_000002_create_billing_clients_table.php`
- Create: `app/Models/BillingClient.php`
- Create: `database/factories/BillingClientFactory.php`
- Test: `tests/Feature/Invoicing/BillingClientTest.php`

**Interfaces:**
- Consumes: `App\Invoicing\StateCodes`, `App\Models\Client`
- Produces:
  - `BillingClient` model, `client(): BelongsTo<Client, $this>`
  - `$billingClient->isRegistered(): bool`
  - `$billingClient->placeOfSupplyStateCode(): ?string`
  - `$billingClient->addressLines(): list<string>`
  - `BillingClientFactory` with states `registered(string $stateCode)` and `unregistered()`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Invoicing/BillingClientTest.php`:

```php
<?php

use App\Models\BillingClient;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reads registration off the GSTIN rather than a second column', function () {
    // Two columns that can disagree is one column too many: a client with a
    // GSTIN is registered, by definition.
    expect(BillingClient::factory()->registered('27')->create()->isRegistered())->toBeTrue()
        ->and(BillingClient::factory()->unregistered()->create()->isRegistered())->toBeFalse();
});

it('falls back to the billing state when no place of supply is set', function () {
    $client = BillingClient::factory()->create([
        'place_of_supply_state_code' => null,
        'billing_address_state_code' => '29',
    ]);

    expect($client->placeOfSupplyStateCode())->toBe('29');
});

it('prefers an explicit place of supply over the billing state', function () {
    // A Bengaluru-registered client can commission a Delhi shoot. The override
    // exists because the two genuinely differ, and §12(7) makes which one wins
    // a judgement call rather than a derivation.
    $client = BillingClient::factory()->create([
        'place_of_supply_state_code' => '07',
        'billing_address_state_code' => '29',
    ]);

    expect($client->placeOfSupplyStateCode())->toBe('07');
});

it('optionally points at a logo-wall client and survives its deletion', function () {
    $logo = Client::create(['name' => 'DLF', 'is_active' => true]);
    $billing = BillingClient::factory()->create(['client_id' => $logo->id]);

    expect($billing->client->name)->toBe('DLF');

    $logo->delete();

    expect($billing->fresh())->not->toBeNull()
        ->and($billing->fresh()->client_id)->toBeNull();
});

it('stores extra recipients as a list', function () {
    $client = BillingClient::factory()->create(['cc_emails' => ['accounts@acme.test', 'cfo@acme.test']]);

    expect($client->fresh()->cc_emails)->toBe(['accounts@acme.test', 'cfo@acme.test']);
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Feature/Invoicing/BillingClientTest.php`
Expected: FAIL — `Class "App\Models\BillingClient" not found`.

- [ ] **Step 3: Write the migration**

Create `database/migrations/2026_09_17_000002_create_billing_clients_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The party we bill — deliberately not the `clients` table.
 *
 * `clients` is the public-site logo wall: it carries an order and an active
 * flag and is read on every page render. Bolting a GSTIN and a billing address
 * onto it would widen those queries and leave every showcase row half-empty,
 * so the two stay separate with an optional link between them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_clients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();

            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('gstin', 15)->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('email')->nullable();
            $table->json('cc_emails')->nullable();
            $table->string('phone')->nullable();

            $table->string('billing_address_line1')->nullable();
            $table->string('billing_address_line2')->nullable();
            $table->string('billing_address_city')->nullable();
            $table->string('billing_address_state')->nullable();
            $table->string('billing_address_state_code', 2)->nullable();
            $table->string('billing_address_postal_code', 6)->nullable();
            $table->string('billing_address_country', 2)->default('IN');

            $table->string('place_of_supply_state_code', 2)->nullable();
            $table->unsignedInteger('payment_terms_days')->nullable();
            $table->string('currency', 3)->default('INR');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_clients');
    }
};
```

- [ ] **Step 4: Write the model**

Create `app/Models/BillingClient.php`:

```php
<?php

namespace App\Models;

use App\Invoicing\StateCodes;
use Database\Factories\BillingClientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingClient extends Model
{
    /** @use HasFactory<BillingClientFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'cc_emails' => 'array',
            'is_active' => 'boolean',
            'payment_terms_days' => 'integer',
        ];
    }

    /** The logo-wall row, when this client is also public-facing. */
    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @param  Builder<BillingClient>  $q */
    public function scopeActive(Builder $q): void
    {
        $q->where('is_active', true);
    }

    /** A GSTIN is what registration means — there is no second flag to disagree with. */
    public function isRegistered(): bool
    {
        return filled($this->gstin);
    }

    public function gstinStateCode(): ?string
    {
        return $this->isRegistered() ? substr((string) $this->gstin, 0, 2) : null;
    }

    /**
     * Where the supply lands, in precedence order: the explicit override, the
     * GSTIN's own state, then the billing address. Null is a valid answer —
     * the invoice form asks for one rather than guessing.
     */
    public function placeOfSupplyStateCode(): ?string
    {
        return $this->place_of_supply_state_code
            ?? $this->gstinStateCode()
            ?? $this->billing_address_state_code;
    }

    public function stateLabel(): ?string
    {
        return StateCodes::name($this->billing_address_state_code);
    }

    /** @return list<string> */
    public function addressLines(): array
    {
        $cityLine = trim(implode(', ', array_filter([$this->billing_address_city, $this->billing_address_state])));

        if ($cityLine !== '' && filled($this->billing_address_postal_code)) {
            $cityLine .= ' '.$this->billing_address_postal_code;
        }

        return array_values(array_filter([
            $this->billing_address_line1,
            $this->billing_address_line2,
            $cityLine === '' ? null : $cityLine,
        ], fn (?string $line): bool => filled($line)));
    }
}
```

Note the precedence: the second test sets `place_of_supply_state_code` to null on a factory row that is unregistered by default, so `gstinStateCode()` returns null and the billing state wins. Make sure `BillingClientFactory::definition()` leaves `gstin` null (see next step) or that test will read the GSTIN's state instead.

- [ ] **Step 5: Write the factory**

Create `database/factories/BillingClientFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Invoicing\StateCodes;
use App\Models\BillingClient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingClient>
 */
class BillingClientFactory extends Factory
{
    protected $model = BillingClient::class;

    public function definition(): array
    {
        return [
            'client_id' => null,
            'name' => fake()->company(),
            // Unregistered by default: most of a studio's clients are, and it is
            // the case with the extra Rule 46(e) requirements, so it is the one
            // worth exercising by accident.
            'gstin' => null,
            'email' => fake()->companyEmail(),
            'phone' => '+91 98100 00000',
            'billing_address_line1' => fake()->streetAddress(),
            'billing_address_city' => 'New Delhi',
            'billing_address_state' => 'Delhi',
            'billing_address_state_code' => '07',
            'billing_address_postal_code' => '110024',
            'billing_address_country' => 'IN',
            'place_of_supply_state_code' => null,
            'currency' => 'INR',
            'is_active' => true,
        ];
    }

    public function registered(string $stateCode = '07'): self
    {
        return $this->state(fn (): array => [
            'gstin' => CompanyFactory::gstinFor($stateCode),
            'billing_address_state_code' => $stateCode,
            'billing_address_state' => StateCodes::name($stateCode),
        ]);
    }

    public function unregistered(): self
    {
        return $this->state(fn (): array => ['gstin' => null]);
    }
}
```

- [ ] **Step 6: Run the test and watch it pass**

Run: `./bin/php vendor/bin/pest tests/Feature/Invoicing/BillingClientTest.php`
Expected: PASS, 5 tests.

---

### Task 7: Service items (the rate card)

**Files:**
- Create: `database/migrations/2026_09_17_000003_create_service_items_table.php`
- Create: `app/Models/ServiceItem.php`
- Create: `database/factories/ServiceItemFactory.php`
- Test: `tests/Feature/Invoicing/ServiceItemTest.php`

**Interfaces:**
- Consumes: `App\Invoicing\Money`, `App\Models\Company`
- Produces:
  - `ServiceItem` model, `company(): BelongsTo<Company, $this>`
  - `ServiceItem::scopeForCompany(Builder $q, ?int $companyId): void` — shared items plus that company's own
  - `$item->formattedRate(): string`
  - `ServiceItem::UNITS` — `list<string>`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Invoicing/ServiceItemTest.php`:

```php
<?php

use App\Models\Company;
use App\Models\ServiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores the rate as paise', function () {
    $item = ServiceItem::factory()->create(['rate_paise' => 15000000]);

    expect($item->fresh()->rate_paise)->toBe(15000000)
        ->and($item->formattedRate())->toBe('₹1,50,000.00');
});

it('offers shared items to every company alongside that company own items', function () {
    $mine = Company::factory()->create();
    $theirs = Company::factory()->create();

    ServiceItem::factory()->create(['company_id' => null, 'name' => 'Shared']);
    ServiceItem::factory()->create(['company_id' => $mine->id, 'name' => 'Mine']);
    ServiceItem::factory()->create(['company_id' => $theirs->id, 'name' => 'Theirs']);

    expect(ServiceItem::forCompany($mine->id)->pluck('name')->sort()->values()->all())
        ->toBe(['Mine', 'Shared']);
});

it('defaults to the event photography SAC', function () {
    // 998383 — event photography and videography, 18%. Wrong on an invoice is a
    // filing mismatch, so the default is the one the studio bills most.
    expect(ServiceItem::factory()->create()->sac_code)->toBe('998383');
});

it('marks pass-through costs so the invoice can group them', function () {
    $expense = ServiceItem::factory()->expense()->create();

    expect($expense->is_expense)->toBeTrue();
});

it('scopes to active items', function () {
    ServiceItem::factory()->create(['is_active' => true]);
    ServiceItem::factory()->create(['is_active' => false]);

    expect(ServiceItem::active()->count())->toBe(1);
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Feature/Invoicing/ServiceItemTest.php`
Expected: FAIL — `Class "App\Models\ServiceItem" not found`.

- [ ] **Step 3: Write the migration**

Create `database/migrations/2026_09_17_000003_create_service_items_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The saved rate card an invoice line is built from.
 *
 * `rate_paise` is an integer for the reason every money column here is: the GST
 * split is a three-way division and a float leaves the parts and the total a
 * paisa apart. `tax_rate_bps` is basis points for the same reason — a 2.5%
 * half-rate is the integer 250, where 2.5 as a float is not itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();

            // Null means shared: most of the rate card applies whichever entity
            // issues the invoice.
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sac_code', 8)->nullable()->default('998383');
            $table->string('unit')->default('project');
            $table->bigInteger('rate_paise')->default(0);
            $table->unsignedInteger('tax_rate_bps')->default(1800);
            $table->boolean('is_expense')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};
```

- [ ] **Step 4: Write the model**

Create `app/Models/ServiceItem.php`:

```php
<?php

namespace App\Models;

use App\Invoicing\Money;
use Database\Factories\ServiceItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItem extends Model
{
    /** @use HasFactory<ServiceItemFactory> */
    use HasFactory;

    /** @var list<string> */
    public const UNITS = ['project', 'day', 'hour', 'shoot', 'item'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rate_paise' => 'integer',
            'tax_rate_bps' => 'integer',
            'is_expense' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @param  Builder<ServiceItem>  $q */
    public function scopeActive(Builder $q): void
    {
        $q->where('is_active', true);
    }

    /**
     * Shared items plus the ones belonging to this company.
     *
     * @param  Builder<ServiceItem>  $q
     */
    public function scopeForCompany(Builder $q, ?int $companyId): void
    {
        $q->where(fn (Builder $inner) => $inner->whereNull('company_id')->orWhere('company_id', $companyId));
    }

    public function formattedRate(): string
    {
        return Money::format($this->rate_paise);
    }
}
```

- [ ] **Step 5: Write the factory**

Create `database/factories/ServiceItemFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Invoicing\Money;
use App\Models\ServiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceItem>
 */
class ServiceItemFactory extends Factory
{
    protected $model = ServiceItem::class;

    public function definition(): array
    {
        return [
            'company_id' => null,
            'name' => fake()->randomElement(['Full-day event coverage', 'Half-day shoot', 'Reel edit', 'Brand film']),
            'description' => fake()->sentence(),
            'sac_code' => '998383',
            'unit' => 'project',
            'rate_paise' => Money::fromRupees(fake()->numberBetween(5000, 200000)),
            'tax_rate_bps' => 1800,
            'is_expense' => false,
            'sort' => 0,
            'is_active' => true,
        ];
    }

    public function expense(): self
    {
        return $this->state(fn (): array => [
            'name' => 'Travel and accommodation',
            'is_expense' => true,
        ]);
    }
}
```

- [ ] **Step 6: Run the test and watch it pass**

Run: `./bin/php vendor/bin/pest tests/Feature/Invoicing/ServiceItemTest.php`
Expected: PASS, 5 tests.

---

### Task 8: Billing seeder

**Files:**
- Create: `database/seeders/BillingSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php` — add `BillingSeeder::class` to the `call()` list
- Test: `tests/Feature/Invoicing/BillingSeederTest.php`

**Interfaces:**
- Consumes: `App\Models\Company`, `App\Models\ServiceItem`, `App\Invoicing\Money`
- Produces: one default company and a starter rate card, idempotently.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Invoicing/BillingSeederTest.php`:

```php
<?php

use App\Models\Company;
use App\Models\ServiceItem;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a default company', function () {
    $this->seed(BillingSeeder::class);

    expect(Company::count())->toBe(1)
        ->and(Company::default())->not->toBeNull();
});

it('leaves the tax registration blank rather than inventing one', function () {
    // A seeded placeholder GSTIN would be a fake number on a real invoice. The
    // company arrives unregistered so that phase 2 validation refuses to issue
    // until a human has entered the real details.
    $this->seed(BillingSeeder::class);

    $company = Company::default();

    expect($company->gstin)->toBeNull()
        ->and($company->is_gst_registered)->toBeFalse();
});

it('seeds a starter rate card', function () {
    $this->seed(BillingSeeder::class);

    expect(ServiceItem::count())->toBeGreaterThan(0)
        ->and(ServiceItem::where('is_expense', true)->count())->toBeGreaterThan(0);
});

it('is idempotent', function () {
    // Seeders are this project fixture layer and get re-run on every deploy;
    // a second run must not produce a second company or a duplicated rate card.
    $this->seed(BillingSeeder::class);
    $this->seed(BillingSeeder::class);

    expect(Company::count())->toBe(1)
        ->and(ServiceItem::count())->toBe(ServiceItem::distinct('name')->count('name'));
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Feature/Invoicing/BillingSeederTest.php`
Expected: FAIL — `Class "Database\Seeders\BillingSeeder" not found`.

- [ ] **Step 3: Write the seeder**

Create `database/seeders/BillingSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Invoicing\Money;
use App\Models\Company;
use App\Models\ServiceItem;
use App\Support\Nap;
use Illuminate\Database\Seeder;

/**
 * Brings up a billing entity and a starter rate card.
 *
 * Everything tax-related is deliberately left blank. A seeded GSTIN would be a
 * made-up number sitting on a document that is a legal declaration, so the
 * company arrives `is_gst_registered = false` and phase-2 validation refuses to
 * issue a tax invoice until a human has filled in the real registration.
 */
class BillingSeeder extends Seeder
{
    public function run(): void
    {
        $address = Nap::address() ?? [];

        $company = Company::firstOrCreate(
            ['name' => config('app.name')],
            [
                'is_gst_registered' => false,
                'address_line1' => $address['streetAddress'] ?? null,
                'address_city' => $address['addressLocality'] ?? null,
                'address_state' => $address['addressRegion'] ?? null,
                'address_postal_code' => $address['postalCode'] ?? null,
                'address_country' => $address['addressCountry'] ?? 'IN',
                'email' => config('mail.from.address'),
                'invoice_prefix' => 'INV',
                'credit_note_prefix' => 'CRN',
                'proforma_prefix' => 'PRO',
                'receipt_prefix' => 'RCT',
                'default_template' => 'classic',
                'default_payment_terms_days' => 7,
                'is_active' => true,
            ]
        );

        if (! $company->is_default) {
            $company->makeDefault();
        }

        foreach ($this->rateCard() as $item) {
            ServiceItem::firstOrCreate(
                ['company_id' => null, 'name' => $item['name']],
                $item
            );
        }
    }

    /**
     * Starter lines, all at SAC 998383 / 18% — event photography and
     * videography. Rates are placeholders an admin is expected to edit.
     *
     * @return list<array<string, mixed>>
     */
    private function rateCard(): array
    {
        return [
            ['name' => 'Full-day event coverage', 'unit' => 'day', 'rate_paise' => Money::fromRupees('0'), 'sac_code' => '998383', 'sort' => 10],
            ['name' => 'Half-day shoot', 'unit' => 'day', 'rate_paise' => Money::fromRupees('0'), 'sac_code' => '998383', 'sort' => 20],
            ['name' => 'Brand film', 'unit' => 'project', 'rate_paise' => Money::fromRupees('0'), 'sac_code' => '998383', 'sort' => 30],
            ['name' => 'Reel edit', 'unit' => 'item', 'rate_paise' => Money::fromRupees('0'), 'sac_code' => '998386', 'sort' => 40],
            ['name' => 'Travel and accommodation', 'unit' => 'item', 'rate_paise' => Money::fromRupees('0'), 'sac_code' => '998383', 'sort' => 90, 'is_expense' => true],
        ];
    }
}
```

- [ ] **Step 4: Register it in DatabaseSeeder**

In `database/seeders/DatabaseSeeder.php`, add `BillingSeeder::class` to the `$this->call([...])` array, after `SiteSettingsSeeder::class`:

```php
            SiteSettingsSeeder::class,
            BillingSeeder::class,
            SeoPagesSeeder::class,
```

- [ ] **Step 5: Run the test, then the whole suite**

Run: `./bin/php vendor/bin/pest tests/Feature/Invoicing`
Expected: PASS.

Run: `./bin/php vendor/bin/pest`
Expected: PASS — the existing suite calls `$this->seed()` widely, so this is where a seeder mistake shows up.

---

### Task 9: Billing navigation group and the Company resource

**Files:**
- Modify: `app/Providers/Filament/AdminPanelProvider.php` — add `'Billing'` to `navigationGroups()`
- Create: `app/Filament/Forms/Components/RupeeInput.php`
- Create: `app/Filament/Resources/CompanyResource.php`
- Create: `app/Filament/Resources/CompanyResource/Pages/ListCompanies.php`
- Create: `app/Filament/Resources/CompanyResource/Pages/CreateCompany.php`
- Create: `app/Filament/Resources/CompanyResource/Pages/EditCompany.php`
- Test: `tests/Feature/Admin/CompanyResourceTest.php`

**Interfaces:**
- Consumes: `App\Models\Company`, `App\Invoicing\StateCodes`, `App\Rules\Gstin`, `App\Invoicing\Money`
- Produces: `RupeeInput::make(string $name): TextInput` — a rupee field that stores paise. Used by the ServiceItem resource in Task 11 and by every money field in phase 2.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/CompanyResourceTest.php`:

```php
<?php

use App\Filament\Resources\CompanyResource\Pages\CreateCompany;
use App\Filament\Resources\CompanyResource\Pages\ListCompanies;
use App\Models\Company;
use App\Models\User;
use Database\Factories\CompanyFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list companies', function () {
    Livewire::test(ListCompanies::class)->assertCanSeeTableRecords(Company::all());
});

it('creates a company with a valid GSTIN', function () {
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'TheLastClicks Studio',
            'is_gst_registered' => true,
            'gstin' => CompanyFactory::gstinFor('07'),
            'address_state_code' => '07',
            'invoice_prefix' => 'TLC',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Company::where('name', 'TheLastClicks Studio')->exists())->toBeTrue();
});

it('rejects a GSTIN whose checksum is wrong', function () {
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Typo Studio',
            'is_gst_registered' => true,
            'gstin' => '07AAACT2727Q1ZW',
            'address_state_code' => '07',
        ])
        ->call('create')
        ->assertHasFormErrors(['gstin']);
});

it('rejects a GSTIN from a different state than the address', function () {
    // The invoice reads the state code to decide CGST+SGST versus IGST. A GSTIN
    // and an address that disagree make that decision wrong in a way nobody
    // notices until a return is filed.
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Mismatched Studio',
            'is_gst_registered' => true,
            'gstin' => CompanyFactory::gstinFor('27'),
            'address_state_code' => '07',
        ])
        ->call('create')
        ->assertHasFormErrors(['gstin']);
});

it('rejects a prefix that would overflow the 16-character invoice number', function () {
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Long Prefix Studio',
            'is_gst_registered' => false,
            'invoice_prefix' => 'TOOLONG',
        ])
        ->call('create')
        ->assertHasFormErrors(['invoice_prefix']);
});

it('switches the default from the table', function () {
    $first = Company::factory()->create();
    $second = Company::factory()->create();

    Livewire::test(ListCompanies::class)
        ->callTableAction('makeDefault', $second);

    expect($second->fresh()->is_default)->toBeTrue()
        ->and($first->fresh()->is_default)->toBeFalse();
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Feature/Admin/CompanyResourceTest.php`
Expected: FAIL — pages not found.

- [ ] **Step 3: Add the Billing navigation group**

In `app/Providers/Filament/AdminPanelProvider.php`, change the `navigationGroups()` call to:

```php
            // Leads first — the panel exists to chase work, not to browse content.
            // Billing sits next to it because an invoice is the last step of the
            // same job.
            ->navigationGroups([
                'Leads',
                'Billing',
                'Content',
                'Site',
                'Access',
            ])
```

- [ ] **Step 4: Write the rupee input**

Create `app/Filament/Forms/Components/RupeeInput.php`:

```php
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
            ->rule('regex:/^-?[0-9,]*\.?[0-9]{0,2}$/')
            ->default(0)
            ->formatStateUsing(fn (int|string|null $state): string => Money::toRupees((int) ($state ?? 0)))
            ->dehydrateStateUsing(fn (int|string|null $state): int => Money::fromRupees($state));
    }
}
```

- [ ] **Step 5: Write the resource**

Create `app/Filament/Resources/CompanyResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Invoicing\StateCodes;
use App\Models\Company;
use App\Rules\Gstin;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Companies';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identity')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255)
                    ->helperText('Trading name, as it appears at the top of the invoice.'),
                TextInput::make('legal_name')->maxLength(255)
                    ->helperText('Only if it differs from the trading name.'),
                TextInput::make('email')->email()->maxLength(255),
                TextInput::make('phone')->maxLength(50),
                TextInput::make('website')->url()->maxLength(255),
            ]),

            Section::make('Tax registration')->columns(2)->schema([
                Toggle::make('is_gst_registered')->default(true)->live()
                    ->helperText('Off means this entity cannot issue a tax invoice.'),
                TextInput::make('gstin')
                    ->label('GSTIN')
                    ->maxLength(15)
                    ->visible(fn (Get $get): bool => (bool) $get('is_gst_registered'))
                    ->required(fn (Get $get): bool => (bool) $get('is_gst_registered'))
                    // The state rule is the load-bearing one: the invoice reads
                    // this state code to choose CGST+SGST or IGST.
                    ->rules(fn (Get $get): array => [new Gstin($get('address_state_code'))]),
                TextInput::make('pan')->label('PAN')->maxLength(10),
                TextInput::make('cin')->label('CIN')->maxLength(21),
                TextInput::make('lut_number')->label('LUT number')
                    ->helperText('Needed to invoice an export without IGST.'),
                DatePicker::make('lut_valid_till'),
            ]),

            Section::make('Address')->columns(2)->schema([
                TextInput::make('address_line1')->label('Address line 1')->maxLength(255),
                TextInput::make('address_line2')->label('Address line 2')->maxLength(255),
                TextInput::make('address_city')->maxLength(255),
                Select::make('address_state_code')
                    ->label('State')
                    ->options(StateCodes::options())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('address_state', StateCodes::name($state))),
                TextInput::make('address_state')->label('State name')->maxLength(255)
                    ->helperText('Filled from the state above; printed on the invoice.'),
                TextInput::make('address_postal_code')->maxLength(6),
            ]),

            Section::make('Bank and UPI')->columns(2)->schema([
                TextInput::make('bank_name')->maxLength(255),
                TextInput::make('bank_account_name')->maxLength(255),
                TextInput::make('bank_account_number')->maxLength(34),
                TextInput::make('bank_ifsc')->label('IFSC')->maxLength(11),
                TextInput::make('bank_branch')->maxLength(255),
                TextInput::make('upi_id')->label('UPI ID')
                    ->helperText('Becomes the QR code on the invoice PDF.'),
            ]),

            Section::make('Branding')->columns(3)->schema([
                SpatieMediaLibraryFileUpload::make('logo')->collection('logo')->image(),
                SpatieMediaLibraryFileUpload::make('signature')->collection('signature')->image()
                    ->helperText('Transparent PNG. Sits in the authorised-signatory box.'),
                SpatieMediaLibraryFileUpload::make('stamp')->collection('stamp')->image(),
            ]),

            Section::make('Invoice defaults')->columns(2)->schema([
                // Five characters is the ceiling: PREFX/26-27/0001 is exactly the
                // 16 characters Rule 46(b) allows for an invoice number.
                TextInput::make('invoice_prefix')->required()->maxLength(5)->default('INV')
                    ->rule('regex:/^[A-Za-z0-9-]+$/')
                    ->helperText('Up to 5 characters. Produces numbers like TLC/26-27/001.'),
                TextInput::make('credit_note_prefix')->required()->maxLength(5)->default('CRN')->rule('regex:/^[A-Za-z0-9-]+$/'),
                TextInput::make('proforma_prefix')->required()->maxLength(5)->default('PRO')->rule('regex:/^[A-Za-z0-9-]+$/'),
                TextInput::make('receipt_prefix')->required()->maxLength(5)->default('RCT')->rule('regex:/^[A-Za-z0-9-]+$/'),
                Select::make('default_template')
                    ->options(['classic' => 'Classic', 'modern' => 'Modern', 'minimal' => 'Minimal'])
                    ->default('classic')->required(),
                TextInput::make('default_payment_terms_days')->numeric()->default(7)->required(),
                Textarea::make('default_terms')->rows(3)->columnSpanFull(),
                Textarea::make('default_notes')->rows(2)->columnSpanFull(),
                Textarea::make('footer_note')->rows(2)->columnSpanFull(),
            ]),

            Section::make()->columns(2)->schema([
                Toggle::make('is_active')->default(true)
                    // The observer throws rather than letting the default be
                    // switched off, which would leave every invoice form with no
                    // company and no explanation.
                    ->disabled(fn (?Company $record): bool => (bool) $record?->is_default)
                    ->helperText(fn (?Company $record): ?string => $record?->is_default
                        ? 'The default company cannot be deactivated. Make another company default first.'
                        : 'Hide without deleting.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('gstin')->label('GSTIN')->searchable()
                    ->placeholder('Unregistered'),
                TextColumn::make('address_state')->label('State')->sortable(),
                IconColumn::make('is_default')->boolean()->label('Default'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('name')
            ->actions([
                Action::make('makeDefault')
                    ->label('Make default')
                    ->icon('heroicon-o-star')
                    ->visible(fn (Company $record): bool => ! $record->is_default)
                    ->requiresConfirmation()
                    ->action(fn (Company $record) => $record->makeDefault()),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 6: Write the three pages**

Create `app/Filament/Resources/CompanyResource/Pages/ListCompanies.php`:

```php
<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
```

Create `app/Filament/Resources/CompanyResource/Pages/CreateCompany.php`:

```php
<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;
}
```

Create `app/Filament/Resources/CompanyResource/Pages/EditCompany.php`:

```php
<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
```

- [ ] **Step 7: Run the test and watch it pass**

Run: `./bin/php vendor/bin/pest tests/Feature/Admin/CompanyResourceTest.php`
Expected: PASS, 6 tests. If the `makeDefault` action assertion fails on the action name, confirm it matches `Action::make('makeDefault')` exactly.

---

### Task 10: Billing client resource

**Files:**
- Create: `app/Filament/Resources/BillingClientResource.php`
- Create: `app/Filament/Resources/BillingClientResource/Pages/ListBillingClients.php`
- Create: `app/Filament/Resources/BillingClientResource/Pages/CreateBillingClient.php`
- Create: `app/Filament/Resources/BillingClientResource/Pages/EditBillingClient.php`
- Test: `tests/Feature/Admin/BillingClientResourceTest.php`

**Interfaces:**
- Consumes: `App\Models\BillingClient`, `App\Models\Client`, `App\Invoicing\StateCodes`, `App\Rules\Gstin`
- Produces: nothing other tasks consume.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/BillingClientResourceTest.php`:

```php
<?php

use App\Filament\Resources\BillingClientResource\Pages\CreateBillingClient;
use App\Filament\Resources\BillingClientResource\Pages\ListBillingClients;
use App\Models\BillingClient;
use App\Models\User;
use Database\Factories\CompanyFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list billing clients', function () {
    BillingClient::factory()->count(2)->create();

    Livewire::test(ListBillingClients::class)->assertCanSeeTableRecords(BillingClient::all());
});

it('creates an unregistered client without a GSTIN', function () {
    // Most of a studio clients are individuals with no registration. Requiring
    // a GSTIN here would make the common case the hard one.
    Livewire::test(CreateBillingClient::class)
        ->fillForm([
            'name' => 'Ananya Sharma',
            'email' => 'ananya@example.test',
            'billing_address_state_code' => '07',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(BillingClient::where('name', 'Ananya Sharma')->first()->isRegistered())->toBeFalse();
});

it('rejects a malformed GSTIN', function () {
    Livewire::test(CreateBillingClient::class)
        ->fillForm([
            'name' => 'Acme Events',
            'gstin' => '27AAPFU0939F1ZZ',
            'billing_address_state_code' => '27',
        ])
        ->call('create')
        ->assertHasFormErrors(['gstin']);
});

it('accepts a valid GSTIN from any state, because the client may be anywhere', function () {
    Livewire::test(CreateBillingClient::class)
        ->fillForm([
            'name' => 'Acme Events',
            'gstin' => CompanyFactory::gstinFor('29'),
            'billing_address_state_code' => '29',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Feature/Admin/BillingClientResourceTest.php`
Expected: FAIL — pages not found.

- [ ] **Step 3: Write the resource**

Create `app/Filament/Resources/BillingClientResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingClientResource\Pages;
use App\Invoicing\StateCodes;
use App\Models\BillingClient;
use App\Models\Client;
use App\Rules\Gstin;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillingClientResource extends Resource
{
    protected static ?string $model = BillingClient::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $modelLabel = 'billing client';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identity')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('legal_name')->maxLength(255)
                    ->helperText('The name on their GST registration, if it differs.'),
                TextInput::make('gstin')->label('GSTIN')->maxLength(15)
                    // No state argument here, unlike the company form: a client
                    // can be registered anywhere, and their GSTIN state is what
                    // decides the place of supply rather than contradicting it.
                    ->rules([new Gstin])
                    ->helperText('Leave blank for an unregistered client.'),
                TextInput::make('pan')->label('PAN')->maxLength(10),
                Select::make('client_id')
                    ->label('Logo-wall entry')
                    ->options(fn (): array => Client::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->helperText('Optional link to the public site client logo.'),
            ]),

            Section::make('Contact')->columns(2)->schema([
                TextInput::make('email')->email()->maxLength(255)
                    ->helperText('Where the invoice is sent.'),
                TagsInput::make('cc_emails')->label('CC')
                    ->helperText('Accounts, CA, anyone else who should receive it.'),
                TextInput::make('phone')->maxLength(50),
            ]),

            Section::make('Billing address')->columns(2)->schema([
                TextInput::make('billing_address_line1')->label('Address line 1')->maxLength(255),
                TextInput::make('billing_address_line2')->label('Address line 2')->maxLength(255),
                TextInput::make('billing_address_city')->label('City')->maxLength(255),
                Select::make('billing_address_state_code')
                    ->label('State')
                    ->options(StateCodes::options())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('billing_address_state', StateCodes::name($state))),
                TextInput::make('billing_address_state')->label('State name')->maxLength(255),
                TextInput::make('billing_address_postal_code')->label('PIN')->maxLength(6),
                TextInput::make('billing_address_country')->label('Country')->default('IN')->maxLength(2)
                    ->helperText('Anything other than IN makes the invoice an export.'),
            ]),

            Section::make('Billing defaults')->columns(2)->schema([
                Select::make('place_of_supply_state_code')
                    ->label('Place of supply')
                    ->options(StateCodes::options())
                    ->searchable()
                    ->helperText('Leave blank to use their GSTIN state, then their billing state.'),
                TextInput::make('payment_terms_days')->numeric()
                    ->helperText('Blank uses the company default.'),
                TextInput::make('currency')->default('INR')->maxLength(3),
                Textarea::make('notes')->rows(3)->columnSpanFull(),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('gstin')->label('GST')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Registered' : 'Unregistered')
                    ->color(fn (?string $state): string => filled($state) ? 'success' : 'gray'),
                TextColumn::make('billing_address_state')->label('State')->sortable(),
                TextColumn::make('email')->searchable(),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('name')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillingClients::route('/'),
            'create' => Pages\CreateBillingClient::route('/create'),
            'edit' => Pages\EditBillingClient::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 4: Write the three pages**

Create `app/Filament/Resources/BillingClientResource/Pages/ListBillingClients.php`:

```php
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
```

Create `app/Filament/Resources/BillingClientResource/Pages/CreateBillingClient.php`:

```php
<?php

namespace App\Filament\Resources\BillingClientResource\Pages;

use App\Filament\Resources\BillingClientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBillingClient extends CreateRecord
{
    protected static string $resource = BillingClientResource::class;
}
```

Create `app/Filament/Resources/BillingClientResource/Pages/EditBillingClient.php`:

```php
<?php

namespace App\Filament\Resources\BillingClientResource\Pages;

use App\Filament\Resources\BillingClientResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBillingClient extends EditRecord
{
    protected static string $resource = BillingClientResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
```

- [ ] **Step 5: Run the test and watch it pass**

Run: `./bin/php vendor/bin/pest tests/Feature/Admin/BillingClientResourceTest.php`
Expected: PASS, 4 tests.

---

### Task 11: Service item resource

**Files:**
- Create: `app/Filament/Resources/ServiceItemResource.php`
- Create: `app/Filament/Resources/ServiceItemResource/Pages/ListServiceItems.php`
- Create: `app/Filament/Resources/ServiceItemResource/Pages/CreateServiceItem.php`
- Create: `app/Filament/Resources/ServiceItemResource/Pages/EditServiceItem.php`
- Test: `tests/Feature/Admin/ServiceItemResourceTest.php`

**Interfaces:**
- Consumes: `App\Models\ServiceItem`, `App\Models\Company`, `App\Filament\Forms\Components\RupeeInput`, `App\Invoicing\Money`
- Produces: nothing other tasks consume.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/ServiceItemResourceTest.php`:

```php
<?php

use App\Filament\Resources\ServiceItemResource\Pages\CreateServiceItem;
use App\Filament\Resources\ServiceItemResource\Pages\EditServiceItem;
use App\Filament\Resources\ServiceItemResource\Pages\ListServiceItems;
use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('email', config('app.admin_seed_email'))->first();
    $this->actingAs($this->admin);
});

it('Super-admin can list service items', function () {
    Livewire::test(ListServiceItems::class)->assertCanSeeTableRecords(ServiceItem::all());
});

it('stores a typed rupee rate as paise', function () {
    // The column is paise; the field is rupees. If that conversion is ever
    // dropped, a ₹1,50,000 rate silently becomes ₹1,500 and every invoice built
    // from it is wrong by two orders of magnitude.
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Wedding full-day coverage',
            'unit' => 'day',
            'rate_paise' => '1,50,000',
            'tax_rate_bps' => 1800,
            'sac_code' => '998383',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ServiceItem::where('name', 'Wedding full-day coverage')->first()->rate_paise)->toBe(15000000);
});

it('shows the stored paise back as rupees when editing', function () {
    $item = ServiceItem::factory()->create(['rate_paise' => 15000000]);

    Livewire::test(EditServiceItem::class, ['record' => $item->getRouteKey()])
        ->assertFormSet(['rate_paise' => '150000.00']);
});

it('rejects a rate with three decimal places', function () {
    Livewire::test(CreateServiceItem::class)
        ->fillForm([
            'name' => 'Odd rate',
            'rate_paise' => '100.555',
        ])
        ->call('create')
        ->assertHasFormErrors(['rate_paise']);
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Feature/Admin/ServiceItemResourceTest.php`
Expected: FAIL — pages not found.

- [ ] **Step 3: Write the resource**

Create `app/Filament/Resources/ServiceItemResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\RupeeInput;
use App\Filament\Resources\ServiceItemResource\Pages;
use App\Invoicing\Money;
use App\Models\Company;
use App\Models\ServiceItem;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceItemResource extends Resource
{
    protected static ?string $model = ServiceItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Rate card';

    protected static ?string $modelLabel = 'service item';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255),
                Select::make('company_id')
                    ->label('Company')
                    ->options(fn (): array => Company::orderBy('name')->pluck('name', 'id')->all())
                    ->placeholder('Shared across all companies')
                    ->helperText('Leave blank unless this rate belongs to one entity.'),
                Textarea::make('description')->rows(2)->columnSpanFull()
                    ->helperText('Prefills the invoice line description.'),
                RupeeInput::make('rate_paise')->label('Rate')->required(),
                Select::make('unit')
                    ->options(array_combine(ServiceItem::UNITS, array_map(ucfirst(...), ServiceItem::UNITS)))
                    ->default('project')->required(),
                TextInput::make('sac_code')->label('SAC')->maxLength(8)->default('998383')
                    ->helperText('998383 is event photography and videography.'),
                Select::make('tax_rate_bps')
                    ->label('GST rate')
                    ->options([0 => '0%', 500 => '5%', 1200 => '12%', 1800 => '18%', 2800 => '28%'])
                    ->default(1800)->required(),
                Toggle::make('is_expense')
                    ->label('Reimbursable expense')
                    ->helperText('Groups this line under expenses on the invoice. Does not change the tax.'),
                TextInput::make('sort')->numeric()->default(0),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort')->sortable()->label('#'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('company.name')->label('Company')->placeholder('Shared'),
                TextColumn::make('rate_paise')->label('Rate')
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->sortable(),
                TextColumn::make('unit'),
                TextColumn::make('sac_code')->label('SAC'),
                TextColumn::make('tax_rate_bps')->label('GST')
                    ->formatStateUsing(fn (int $state): string => ($state / 100).'%'),
                IconColumn::make('is_expense')->boolean()->label('Expense'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceItems::route('/'),
            'create' => Pages\CreateServiceItem::route('/create'),
            'edit' => Pages\EditServiceItem::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 4: Write the three pages**

Create `app/Filament/Resources/ServiceItemResource/Pages/ListServiceItems.php`:

```php
<?php

namespace App\Filament\Resources\ServiceItemResource\Pages;

use App\Filament\Resources\ServiceItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceItems extends ListRecords
{
    protected static string $resource = ServiceItemResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
```

Create `app/Filament/Resources/ServiceItemResource/Pages/CreateServiceItem.php`:

```php
<?php

namespace App\Filament\Resources\ServiceItemResource\Pages;

use App\Filament\Resources\ServiceItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceItem extends CreateRecord
{
    protected static string $resource = ServiceItemResource::class;
}
```

Create `app/Filament/Resources/ServiceItemResource/Pages/EditServiceItem.php`:

```php
<?php

namespace App\Filament\Resources\ServiceItemResource\Pages;

use App\Filament\Resources\ServiceItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceItem extends EditRecord
{
    protected static string $resource = ServiceItemResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
```

- [ ] **Step 5: Run the test and watch it pass**

Run: `./bin/php vendor/bin/pest tests/Feature/Admin/ServiceItemResourceTest.php`
Expected: PASS, 4 tests.

---

### Task 12: Permissions

**Files:**
- Modify: `database/seeders/RolesSeeder.php` — add the `Accounts` role
- Modify: `database/seeders/PermissionsSeeder.php` — grant billing permissions to Accounts, withhold them from Viewer
- Test: `tests/Feature/Admin/BillingPermissionsTest.php`

**Interfaces:**
- Consumes: the three resources from Tasks 9–11.
- Produces: an `Accounts` role holding every `*_company`, `*_billing::client` and `*_service::item` permission.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/BillingPermissionsTest.php`:

```php
<?php

use App\Models\BillingClient;
use App\Models\Company;
use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('gives the Accounts role the billing resources', function () {
    $accounts = User::factory()->create();
    $accounts->assignRole('Accounts');

    expect($accounts->can('viewAny', Company::class))->toBeTrue()
        ->and($accounts->can('create', BillingClient::class))->toBeTrue()
        ->and($accounts->can('update', ServiceItem::factory()->create()))->toBeTrue();
});

it('keeps billing away from Editor', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    expect($editor->can('viewAny', Company::class))->toBeFalse()
        ->and($editor->can('viewAny', BillingClient::class))->toBeFalse();
});

it('keeps billing away from Viewer, despite Viewer holding every other view permission', function () {
    // Viewer is granted every `view_*` permission by a blanket filter. Billing
    // carries bank details and client GSTINs, so it is excluded explicitly —
    // otherwise read-only quietly means "can read our bank account".
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');

    expect($viewer->can('viewAny', Company::class))->toBeFalse()
        ->and($viewer->can('viewAny', BillingClient::class))->toBeFalse()
        ->and($viewer->can('viewAny', ServiceItem::class))->toBeFalse();
});

it('still gives Super-admin everything', function () {
    $admin = User::where('email', config('app.admin_seed_email'))->first();

    expect($admin->can('viewAny', Company::class))->toBeTrue();
});
```

- [ ] **Step 2: Run the test and watch it fail**

Run: `./bin/php vendor/bin/pest tests/Feature/Admin/BillingPermissionsTest.php`
Expected: FAIL — the `Accounts` role does not exist, and Viewer can see companies.

- [ ] **Step 3: Add the Accounts role**

In `database/seeders/RolesSeeder.php`:

```php
        foreach (['Super-admin', 'Editor', 'Sales', 'Viewer', 'Accounts'] as $name) {
            Role::findOrCreate($name, 'web');
        }
```

- [ ] **Step 4: Wire the billing permissions**

In `database/seeders/PermissionsSeeder.php`, inside `assignRolePermissions()`:

Add the role lookup next to the others:

```php
        $accounts = Role::findOrCreate('Accounts', 'web');
```

Add this constant near `$leadDeskPermissions`:

```php
    /**
     * Shield derives a permission suffix from the resource name, so two-word
     * models land as `billing::client`, not `billing_client`. Matching the
     * underscore form silently grants nothing.
     */
    protected string $billingResources = 'company|billing::client|service::item';
```

Grant them to Accounts, after the Sales block:

```php
        // Accounts: the billing surface, and only that. Invoices carry bank
        // details and client GSTINs, which is a narrower audience than content
        // or leads.
        $accounts->syncPermissions(array_filter(
            $all,
            fn ($p) => preg_match('/_('.$this->billingResources.')$/', $p) === 1
        ));
```

Then exclude billing from Viewer's blanket read grant:

```php
        // Viewer: read-only everywhere, including the lead desk. Moving a card is
        // still refused by QuotePolicy::update, which Viewer never satisfies.
        // Billing is carved out: a blanket `view_*` grant would hand every
        // read-only account our bank details and every client's GSTIN.
        $viewer->syncPermissions(array_merge(
            array_filter(
                $all,
                fn ($p) => str_starts_with($p, 'view_')
                    && preg_match('/_('.$this->billingResources.')$/', $p) !== 1
            ),
            $leadDesk,
        ));
```

- [ ] **Step 5: Run the test and watch it pass**

Run: `./bin/php vendor/bin/pest tests/Feature/Admin/BillingPermissionsTest.php`
Expected: PASS, 4 tests.

- [ ] **Step 6: Check nothing else moved**

Run: `./bin/php vendor/bin/pest tests/Feature/Admin`
Expected: PASS — `ShieldPermissionTest` and `AdminPanelAccessTest` cover the roles this task edits.

---

### Task 13: Full verification and documentation

**Files:**
- Modify: `CLAUDE.md` — add a Billing section under Architecture

**Interfaces:**
- Consumes: everything above.
- Produces: a green CI triple and a codebase note for the next person.

- [ ] **Step 1: Run the three CI commands, in CI order**

```bash
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M --no-progress
./bin/php vendor/bin/pest --no-coverage
```

Expected: all three pass. Fix, then re-run all three — a pint fix can change a line phpstan reads.

- [ ] **Step 2: Confirm the migrations apply to an empty database**

Run: `DB_CONNECTION=sqlite DB_DATABASE=$(mktemp -t billing-check).sqlite ./bin/php artisan migrate:fresh --seed`
Expected: all migrations run in order and `BillingSeeder` creates one default company. A scratch file rather than `:memory:`, because an in-memory database is torn down before anything can be asserted about it. This catches a migration-ordering mistake that `RefreshDatabase` hides.

- [ ] **Step 3: Document the subsystem**

In `CLAUDE.md`, add after the **Media** section:

```markdown
### Billing

`companies` (our entities, exactly one `is_default`), `billing_clients` (who we bill — *not*
`clients`, which is the public logo wall) and `service_items` (the saved rate card).

Money is **integer paise** in `*_paise` bigint columns, tax rates are **basis points**
(`18%` → `1800`), and `App\Invoicing\Money` is the only converter. Nothing casts money to a
float: the GST split is a three-way division and a float leaves the components and the grand
total a paisa apart, which is what a GSTR-1 reconciliation surfaces months later on a
document the law no longer lets us edit.

`App\Invoicing\StateCodes` omits 25 and 28 deliberately — both were merged away and cannot
appear in a GSTIN issued today. `App\Rules\Gstin` validates format, state code and the
mod-36 check digit; the checksum is the only part that catches a transposed character, which
is the error people actually make.

`CompanyObserver` holds "exactly one active default" — MySQL has no partial unique index, so
it is a code path, not a constraint. Deactivating the default throws rather than leaving
`Company::default()` null and every invoice form blank with no explanation.

Billing permissions are withheld from Viewer on purpose: its blanket `view_*` grant would
otherwise hand every read-only account our bank details and every client's GSTIN.
```

- [ ] **Step 4: Confirm the admin renders**

Run: `composer dev`, open `/admin`, and check that **Billing** appears between Leads and Content with Clients, Companies and Rate card under it. Create a company, make it default, add a rate-card line at ₹1,50,000 and confirm the table shows `₹1,50,000.00`.

Stop the dev server when done. Leave everything uncommitted for review.

---

## Phase 1 exit criteria

- `./vendor/bin/pint --test`, phpstan level 6 and the full Pest suite all pass.
- The Billing group exists in the admin with three working resources.
- Exactly one company is default and the invariant cannot be broken from the UI.
- `Money`, `StateCodes`, `Gstin` and `RupeeInput` exist with unit tests — phase 2's tax engine and number allocator build directly on them.
