<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreQuoteRequest;
use App\Mail\NewQuoteAdminNotification;
use App\Mail\QuoteAutoReply;
use App\Models\Quote;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * JSON port of Public\ContactController::store().
 *
 * The guard order is load bearing: rate limit before any other work, honeypot
 * before validation. A honeypot hit returns the same 201 a real submission
 * does, so a bot cannot distinguish a silent drop from a save.
 */
class ContactController extends Controller
{
    /** Matches the Blade controller exactly — five per minute per IP. */
    private const MAX_PER_MINUTE = 5;

    public function store(Request $request): JsonResponse
    {
        $key = 'contact:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_PER_MINUTE)) {
            return response()->json(['message' => 'Too many requests.'], 429);
        }
        RateLimiter::hit($key, 60);

        if (filled($request->input('website'))) {
            return response()->json([
                'data' => ['id' => null],
                'message' => 'Thanks — we will be in touch.',
            ], 201);
        }

        // Matches the Blade controller: the full FormRequest pipeline is not
        // used because honeypot and rate limiting have to run first. app()
        // resolves through the container, so contextual bindings still apply.
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
            ->sendToDatabase(User::role('Super-admin')->get());

        return response()->json([
            'data' => ['id' => $quote->id],
            'message' => 'Thanks — we will be in touch.',
        ], 201);
    }
}
