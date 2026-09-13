<?php

use App\Mail\OutreachMail;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->dir = base_path('tests/tmp-outreach');
    if (! is_dir($this->dir)) {
        mkdir($this->dir, 0755, true);
    }

    config([
        'outreach.queue_path' => $this->dir.'/queue.csv',
        'outreach.sent_path' => $this->dir.'/sent.csv',
        'outreach.suppression_path' => $this->dir.'/suppression.txt',
        'outreach.throttle_seconds' => 0,
        'outreach.throttle_jitter' => 0,
    ]);
});

afterEach(function () {
    foreach (glob($this->dir.'/*') ?: [] as $file) {
        unlink($file);
    }
    @rmdir($this->dir);
});

function queueRow(array $overrides = []): array
{
    return array_merge([
        'email' => 'organiser@example.com',
        'organizer' => 'MESSE FRANKFURT TRADE FAIRS INDIA PRIVATE LIMITED',
        'organizer_short' => 'Messe Frankfurt team',
        'event' => 'Media Expo New Delhi 2026',
        'event_city' => 'New Delhi',
        'event_dates' => '17 Sep 2026',
        'days_out' => '40',
        'days_out_phrase' => 'about 6 weeks',
        'event_count' => '8',
        'locations' => 'New Delhi, Mumbai',
        'locations_phrase' => 'across New Delhi, Mumbai',
        'step' => 'first-touch',
        'thread_message_id' => '',
        'thread_subject' => '',
    ], $overrides);
}

function writeQueue(array $rows): void
{
    $handle = fopen(config('outreach.queue_path'), 'w');
    fputcsv($handle, array_keys($rows[0]), ',', '"', '');
    foreach ($rows as $row) {
        fputcsv($handle, array_values($row), ',', '"', '');
    }
    fclose($handle);
}

it('renders the first touch with the recipient\'s own details', function () {
    $body = (new OutreachMail(queueRow(), 'first-touch', 'Subject', 'id@thelastclicks.com'))->render();

    expect($body)
        ->toContain('Hi Messe Frankfurt team,')
        ->toContain('Media Expo New Delhi 2026')
        ->toContain('New Delhi')
        ->toContain('about 6 weeks')
        ->toContain('8 shows across New Delhi, Mumbai');
});

it('always offers a way out', function () {
    foreach (config('outreach.steps') as $step) {
        $body = (new OutreachMail(queueRow(['step' => $step]), $step, 'S', 'id@x.com'))->render();

        expect($body)->toContain('STOP');
    }
});

it('makes no promise about price or turnaround', function () {
    // Standing rule for every public-facing surface: cost and timing depend on
    // the project, so quoting either in a cold email writes a cheque the studio
    // has not agreed to.
    foreach (config('outreach.steps') as $step) {
        $body = strtolower((new OutreachMail(queueRow(['step' => $step]), $step, 'S', 'id@x.com'))->render());

        expect($body)
            ->not->toContain('₹')
            ->not->toContain('within 24')
            ->not->toContain('48 hours')
            ->not->toContain('guarantee');
    }
});

it('sends plain text with no HTML body', function () {
    $mail = new OutreachMail(queueRow(), 'first-touch', 'Subject', 'id@thelastclicks.com');
    $built = $mail->render();

    // An HTML part invites image blocking and tracking suspicion; a cold mail
    // that looks hand-typed both delivers and reads better.
    expect($built)->not->toContain('<html')->not->toContain('<body');
});

it('carries one-click unsubscribe headers', function () {
    $headers = (new OutreachMail(queueRow(), 'first-touch', 'S', 'id@x.com'))->headers();

    expect($headers->text)
        ->toHaveKey('List-Unsubscribe')
        ->toHaveKey('List-Unsubscribe-Post');
    expect($headers->text['List-Unsubscribe-Post'])->toBe('List-Unsubscribe=One-Click');
});

it('threads follow-ups onto the original message', function () {
    $headers = (new OutreachMail(
        queueRow(['step' => 'follow-up-1']),
        'follow-up-1',
        'Re: Subject',
        'new@x.com',
        'original@x.com',
    ))->headers();

    expect($headers->text['In-Reply-To'])->toBe('<original@x.com>')
        ->and($headers->text['References'])->toBe('<original@x.com>');
});

it('does not thread the first touch', function () {
    $headers = (new OutreachMail(queueRow(), 'first-touch', 'S', 'id@x.com'))->headers();

    expect($headers->text)->not->toHaveKey('In-Reply-To');
});

it('sends nothing without --live', function () {
    Mail::fake();
    writeQueue([queueRow()]);

    $this->artisan('outreach:send')
        ->expectsOutputToContain('DRY RUN')
        ->assertSuccessful();

    Mail::assertNothingSent();
});

it('skips addresses on the suppression list', function () {
    writeQueue([queueRow(), queueRow(['email' => 'other@example.com'])]);
    file_put_contents(config('outreach.suppression_path'), "# opted out\norganiser@example.com\n");

    $this->artisan('outreach:send')
        ->expectsOutputToContain('on the suppression list')
        ->assertSuccessful();
});

it('never sends the same person the same step twice', function () {
    writeQueue([queueRow()]);

    $handle = fopen(config('outreach.sent_path'), 'w');
    fputcsv($handle, ['sent_at', 'email', 'step', 'status', 'message_id', 'subject', 'error'], ',', '"', '');
    fputcsv($handle, ['2026-09-01', 'organiser@example.com', 'first-touch', 'sent', 'a@b.com', 'S', ''], ',', '"', '');
    fclose($handle);

    $this->artisan('outreach:send')
        ->expectsOutputToContain('already sent')
        ->assertSuccessful();
});

it('retries a step that failed', function () {
    writeQueue([queueRow()]);

    $handle = fopen(config('outreach.sent_path'), 'w');
    fputcsv($handle, ['sent_at', 'email', 'step', 'status', 'message_id', 'subject', 'error'], ',', '"', '');
    fputcsv($handle, ['2026-09-01', 'organiser@example.com', 'first-touch', 'failed', 'a@b.com', 'S', 'timeout'], ',', '"', '');
    fclose($handle);

    $this->artisan('outreach:send')
        ->doesntExpectOutputToContain('already sent')
        ->assertSuccessful();
});

it('refuses an invalid address rather than handing it to the MTA', function () {
    writeQueue([queueRow(['email' => 'not-an-address'])]);

    $this->artisan('outreach:send')
        ->expectsOutputToContain('invalid address')
        ->assertSuccessful();
});

it('caps a run at max_per_run however large the queue', function () {
    config(['outreach.max_per_run' => 2]);
    writeQueue([
        queueRow(['email' => 'a@example.com']),
        queueRow(['email' => 'b@example.com']),
        queueRow(['email' => 'c@example.com']),
    ]);

    $this->artisan('outreach:send')
        ->expectsOutputToContain('capping this run at 2')
        ->assertSuccessful();
});

it('fails preflight when the From domain does not resolve', function () {
    // The regression guard for the bug that started this: MAIL_FROM_ADDRESS was
    // "info@thelastlclicks.com", one stray letter, NXDOMAIN, and under DMARC
    // p=reject every message the app sent was refused outright.
    config(['mail.from.address' => 'info@thelastlclicks.com']);

    $this->artisan('outreach:preflight')
        ->expectsOutputToContain('does not resolve')
        ->assertFailed();
});

it('fails preflight when the From domain and the SMTP login disagree', function () {
    config([
        'mail.from.address' => 'info@example.com',
        'mail.default' => 'smtp',
        'mail.mailers.smtp.username' => 'info@thelastclicks.com',
    ]);

    $this->artisan('outreach:preflight')
        ->expectsOutputToContain('differs from SMTP auth domain')
        ->assertFailed();
});

it('can test-send to an address that is not a prospect', function () {
    // --only filters the queue, so it can never reach your own inbox. Without a
    // separate flag the "send yourself one first" step is impossible to follow.
    Mail::fake();
    writeQueue([queueRow()]);

    $this->artisan('outreach:send --test=me@example.com')
        ->expectsOutputToContain('TEST SEND')
        ->expectsQuestion('Type SEND to confirm', 'SEND')
        ->assertSuccessful();

    Mail::assertSent(OutreachMail::class);
});

it('does not log a test send against the prospect', function () {
    Mail::fake();
    writeQueue([queueRow()]);

    $this->artisan('outreach:send --test=me@example.com')
        ->expectsQuestion('Type SEND to confirm', 'SEND')
        ->assertSuccessful();

    // organiser@example.com never received anything, so a log entry here would
    // suppress their real first touch.
    expect(file_exists(config('outreach.sent_path')))->toBeFalse();
});

it('refuses a test send to a malformed address', function () {
    writeQueue([queueRow()]);

    $this->artisan('outreach:send --test=not-an-address')
        ->expectsOutputToContain('Not a valid address')
        ->assertFailed();
});

it('caps the first day of sending to the start of the warm-up ramp', function () {
    // A mailbox that sends a handful a day going to sixty in one afternoon is
    // the loudest spam signal a small domain can emit — louder than any wording.
    config(['outreach.warmup' => [5, 8, 12]]);
    writeQueue(array_map(fn ($i) => queueRow(['email' => "c{$i}@example.com"]), range(1, 20)));

    $this->artisan('outreach:send')
        ->expectsOutputToContain('capping this run at 5')
        ->assertSuccessful();
});

it('refuses to send once the day\'s warm-up allowance is spent', function () {
    config(['outreach.warmup' => [2]]);
    writeQueue([queueRow(['email' => 'new@example.com'])]);

    $handle = fopen(config('outreach.sent_path'), 'w');
    fputcsv($handle, ['sent_at', 'email', 'step', 'status', 'message_id', 'subject', 'error'], ',', '"', '');
    foreach (['a@example.com', 'b@example.com'] as $email) {
        fputcsv($handle, [now()->toDateString(), $email, 'first-touch', 'sent', 'm@x', 'S', ''], ',', '"', '');
    }
    fclose($handle);

    $this->artisan('outreach:send')
        ->expectsOutputToContain('which is the cap')
        ->assertSuccessful();
});

it('resumes the ramp where it stopped rather than jumping to the end', function () {
    // Day number counts days actually sent on, not days elapsed. Pausing a
    // fortnight must not earn back allowance.
    config(['outreach.warmup' => [5, 8, 40]]);
    writeQueue(array_map(fn ($i) => queueRow(['email' => "c{$i}@example.com"]), range(1, 30)));

    $handle = fopen(config('outreach.sent_path'), 'w');
    fputcsv($handle, ['sent_at', 'email', 'step', 'status', 'message_id', 'subject', 'error'], ',', '"', '');
    fputcsv($handle, [now()->subDays(30)->toDateString(), 'a@example.com', 'first-touch', 'sent', 'm@x', 'S', ''], ',', '"', '');
    fclose($handle);

    $this->artisan('outreach:send')
        ->expectsOutputToContain('capping this run at 8')
        ->assertSuccessful();
});
