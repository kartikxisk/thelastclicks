<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tells the Next.js frontend to drop specific ISR cache tags.
 *
 * Queued, and deliberately failure-tolerant: an editor saving a page in
 * Filament must never see an error because the frontend process happens to be
 * restarting. A missed revalidation self-heals at the next time-based
 * revalidation window.
 */
class RevalidateFrontend implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    /** @param  list<string>  $tags */
    public function __construct(public array $tags) {}

    public function handle(): void
    {
        $url = config('services.frontend.revalidate_url');

        if (blank($url) || $this->tags === []) {
            return;
        }

        try {
            Http::timeout(5)->post($url, [
                'tags' => array_values(array_unique($this->tags)),
                'secret' => config('services.frontend.revalidate_secret'),
            ]);
        } catch (Throwable $e) {
            Log::warning('Frontend revalidation failed', [
                'tags' => $this->tags,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
