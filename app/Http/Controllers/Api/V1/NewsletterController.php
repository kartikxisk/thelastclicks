<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * JSON port of Public\NewsletterController::store(). Same guard order as
 * ContactController, same silent-success honeypot behaviour.
 */
class NewsletterController extends Controller
{
    /** Matches the Blade controller exactly — five per minute per IP. */
    private const MAX_PER_MINUTE = 5;

    public function store(Request $request): JsonResponse
    {
        $key = 'newsletter:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_PER_MINUTE)) {
            return response()->json(['message' => 'Too many requests.'], 429);
        }
        RateLimiter::hit($key, 60);

        if (filled($request->input('website'))) {
            return response()->json(['message' => 'Thanks — you are on the list.'], 201);
        }

        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        // updateOrCreate so re-subscribing never collides with the unique index,
        // and a previously unsubscribed address is reactivated.
        Subscriber::updateOrCreate(
            ['email' => $validated['email']],
            [
                'source_page' => substr((string) $request->input('source_page', '/'), 0, 255),
                'ip' => $request->ip(),
                'ua' => substr((string) $request->userAgent(), 0, 512),
                'unsubscribed_at' => null,
            ]
        );

        return response()->json(['message' => 'Thanks — you are on the list.'], 201);
    }
}
