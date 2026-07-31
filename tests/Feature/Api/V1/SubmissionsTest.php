<?php

use App\Mail\NewQuoteAdminNotification;
use App\Mail\QuoteAutoReply;
use App\Models\Quote;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Mail::fake();
    RateLimiter::clear('contact:127.0.0.1');
    RateLimiter::clear('newsletter:127.0.0.1');
});

function validQuote(array $overrides = []): array
{
    return [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'phone' => '+441234567890',
        'project_type' => 'brand-film',
        'message' => 'We need a brand film for our spring launch.',
        ...$overrides,
    ];
}

it('creates a quote from a valid contact submission', function () {
    $this->postJson('/api/v1/contact', validQuote())
        ->assertCreated()
        ->assertJsonStructure(['data' => ['id'], 'message']);

    expect(Quote::where('email', 'ada@example.com')->exists())->toBeTrue();
});

it('queues the admin notification and the auto-reply', function () {
    $this->postJson('/api/v1/contact', validQuote())->assertCreated();

    Mail::assertQueued(NewQuoteAdminNotification::class);
    Mail::assertQueued(QuoteAutoReply::class);
});

it('records the submitter ip and user agent', function () {
    $this->postJson('/api/v1/contact', validQuote())->assertCreated();

    $quote = Quote::where('email', 'ada@example.com')->firstOrFail();
    expect($quote->ip)->not->toBeNull();
});

it('rejects an invalid contact submission with field errors', function () {
    $this->postJson('/api/v1/contact', ['name' => '', 'email' => 'not-an-email'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email']);
});

it('requires a message', function () {
    $this->postJson('/api/v1/contact', validQuote(['message' => '']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['message']);
});

it('silently accepts a honeypot submission without persisting it', function () {
    $this->postJson('/api/v1/contact', validQuote(['website' => 'http://spam.example']))
        ->assertCreated();

    expect(Quote::where('email', 'ada@example.com')->exists())->toBeFalse();
    Mail::assertNothingQueued();
});

it('rate limits contact submissions at five per minute', function () {
    foreach (range(1, 5) as $i) {
        $this->postJson('/api/v1/contact', validQuote(['email' => "a{$i}@example.com"]))
            ->assertCreated();
    }

    $this->postJson('/api/v1/contact', validQuote())->assertStatus(429);
});

it('accepts a quote submission on the quotes route', function () {
    $this->postJson('/api/v1/quotes', validQuote())->assertCreated();

    expect(Quote::where('email', 'ada@example.com')->exists())->toBeTrue();
});

it('subscribes a valid email', function () {
    $this->postJson('/api/v1/newsletter', ['email' => 'reader@example.com'])
        ->assertCreated()
        ->assertJsonStructure(['message']);

    expect(Subscriber::where('email', 'reader@example.com')->exists())->toBeTrue();
});

it('reactivates a previously unsubscribed address rather than colliding', function () {
    Subscriber::create(['email' => 'reader@example.com', 'unsubscribed_at' => now()]);

    $this->postJson('/api/v1/newsletter', ['email' => 'reader@example.com'])->assertCreated();

    expect(Subscriber::where('email', 'reader@example.com')->firstOrFail()->unsubscribed_at)->toBeNull();
});

it('rejects an invalid newsletter email', function () {
    $this->postJson('/api/v1/newsletter', ['email' => 'nope'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('silently accepts a honeypot newsletter submission without persisting it', function () {
    $this->postJson('/api/v1/newsletter', ['email' => 'reader@example.com', 'website' => 'spam'])
        ->assertCreated();

    expect(Subscriber::where('email', 'reader@example.com')->exists())->toBeFalse();
});

it('rate limits newsletter submissions at five per minute', function () {
    foreach (range(1, 5) as $i) {
        $this->postJson('/api/v1/newsletter', ['email' => "r{$i}@example.com"])->assertCreated();
    }

    $this->postJson('/api/v1/newsletter', ['email' => 'last@example.com'])->assertStatus(429);
});
