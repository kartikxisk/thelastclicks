<?php

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    RateLimiter::clear('contact:127.0.0.1');
    Mail::fake();
});

it('admins receive a Filament database notification when a new quote arrives', function () {
    $admin = User::where('email', config('app.admin_seed_email'))->first();

    $this->post('/contact', [
        'name' => 'Lead',
        'email' => 'lead@ex.com',
        'message' => 'A test brief',
        'website' => '',
    ])->assertRedirect('/thank-you');

    expect(Quote::count())->toBe(1);
    expect($admin->notifications()->count())->toBeGreaterThanOrEqual(1);
});
