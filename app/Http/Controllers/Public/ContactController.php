<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuoteRequest;
use App\Mail\NewQuoteAdminNotification;
use App\Mail\QuoteAutoReply;
use App\Models\Quote;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact');
    }

    public function store(Request $request): RedirectResponse
    {
        // Rate limit BEFORE any other work (5/min/ip)
        $key = 'contact:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            abort(429);
        }
        RateLimiter::hit($key, 60);

        // Honeypot triggered → silently redirect, don't persist or validate
        if (filled($request->input('website'))) {
            return redirect('/thank-you');
        }

        // NOTE: Full FormRequest pipeline (messages, attributes, prepareForValidation, after hooks)
        // is not wired here because honeypot + rate-limit must run first. To get those hooks
        // you would need to move honeypot/rate-limit into dedicated middleware.
        // app() resolves via the IoC container (binding, contextual deps), which is strictly
        // better than `new StoreQuoteRequest()` but still bypasses the HTTP lifecycle.
        $validated = $request->validate(app(StoreQuoteRequest::class)->rules());

        $quote = Quote::create([
            ...$validated,
            'ip' => $request->ip(),
            'ua' => substr((string) $request->userAgent(), 0, 512),
        ]);

        $adminEmail = SiteSetting::get('contact_email', config('mail.from.address'));
        Mail::to($adminEmail)->queue(new NewQuoteAdminNotification($quote));
        Mail::to($quote->email)->queue(new QuoteAutoReply($quote));

        Notification::make()
            ->title('New quote received')
            ->body($quote->name.' — '.($quote->project_type ?: 'Unspecified'))
            ->success()
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->url(route('filament.admin.resources.quotes.edit', ['record' => $quote->id]))
                    ->markAsRead(),
            ])
            ->sendToDatabase(
                User::role('Super-admin')->get()
            );

        return redirect('/thank-you');
    }
}
