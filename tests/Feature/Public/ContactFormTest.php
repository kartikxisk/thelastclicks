<?php

use App\Mail\NewQuoteAdminNotification;
use App\Mail\QuoteAutoReply;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    RateLimiter::clear('contact:127.0.0.1');
    Mail::fake();
});

it('rejects missing required fields', function () {
    $this->post('/contact', [])->assertSessionHasErrors(['name', 'email', 'message']);
});

it('accepts a valid submission, persists Quote, redirects to thank-you', function () {
    $r = $this->post('/contact', [
        'name' => 'Jane',
        'email' => 'jane@example.com',
        'message' => 'Brief for a launch film.',
        'project_type' => 'Brand film / commercial',
        'budget' => '₹15L – ₹50L',
        'timeline' => '1–2 months',
        'source_page' => '/contact',
        'website' => '', // honeypot empty
    ]);
    $r->assertRedirect('/thank-you');
    expect(Quote::count())->toBe(1);
    Mail::assertQueued(NewQuoteAdminNotification::class);
    Mail::assertQueued(QuoteAutoReply::class);
});

it('silently drops bot submissions filling honeypot', function () {
    $this->post('/contact', [
        'name' => 'B', 'email' => 'b@b.com', 'message' => 'x', 'website' => 'spam',
    ])->assertRedirect('/thank-you');
    expect(Quote::count())->toBe(0);
    Mail::assertNothingQueued();
});

it('rate limits beyond 5 per minute per IP', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post('/contact', [
            'name' => "User$i", 'email' => "u$i@x.com", 'message' => 'hi',
        ])->assertRedirect('/thank-you');
    }
    $this->post('/contact', [
        'name' => 'X', 'email' => 'x@x.com', 'message' => 'hi',
    ])->assertStatus(429);
});
