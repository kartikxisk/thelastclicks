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
        ->and(gstinFailure('27AAPFU0939F1AV'))->not->toBeNull()     // 14th char is not Z
        ->and(gstinFailure('AAAAAAAAAAAAAAA'))->not->toBeNull();
});

it('accepts a lowercase GSTIN, because case is not part of the number', function () {
    // This used to be asserted as a rejection, grouped under "wrong shape".
    // Lowercase is not a wrong shape, it is a wrong case, and every issued
    // GSTIN is uppercase — so case carries no information and a paste out of
    // an email should not be refused for it. Form validation runs before the
    // field's dehydration, so uppercasing only on the way into the column
    // would still leave the admin facing "the format is invalid", which names
    // the wrong problem.
    expect(gstinFailure('27aapfu0939f1zv'))->toBeNull()
        ->and(gstinFailure('  27AAPFU0939F1ZV  '))->toBeNull()
        ->and(Gstin::isValid('27aapfu0939f1zv'))->toBeTrue();
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

it('guards checksum() against a stub too short', function () {
    expect(fn () => Gstin::checksum('27AAPFU0939F1'))->toThrow(InvalidArgumentException::class);
});

it('guards checksum() against a stub too long', function () {
    expect(fn () => Gstin::checksum('27AAPFU0939F1ZZZ'))->toThrow(InvalidArgumentException::class);
});

it('returns sentinel on a valid-length stub with an out-of-alphabet character', function () {
    expect(Gstin::checksum('27AAPFU0939F1!'))->toBe('?');  // exactly 14 chars with invalid char at end
});
