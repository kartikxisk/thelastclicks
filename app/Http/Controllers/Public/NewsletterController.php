<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // Rate limit BEFORE any other work (5/min/ip) — mirrors ContactController.
        $key = 'newsletter:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            abort(429);
        }
        RateLimiter::hit($key, 60);

        // Honeypot triggered → report success but persist nothing.
        if (filled($request->input('website'))) {
            return back()->with('newsletter_status', 'Thanks — you are on the list.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        // updateOrCreate so re-subscribing never collides with the unique index,
        // and a previously unsubscribed address is reactivated.
        Subscriber::updateOrCreate(
            ['email' => $validated['email']],
            [
                'source_page' => substr((string) $request->input('source_page', $request->path()), 0, 255),
                'ip' => $request->ip(),
                'ua' => substr((string) $request->userAgent(), 0, 512),
                'unsubscribed_at' => null,
            ]
        );

        return back()->with('newsletter_status', 'Thanks — you are on the list.');
    }
}
