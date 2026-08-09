<?php

namespace App\Jobs;

use App\Support\AppUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tells Bing, Yandex, Naver and Seznam that a URL changed, instead of waiting to
 * be re-crawled. Google does not participate — this is not a Google play; it is
 * the Bing index, which is what Microsoft Copilot cites from.
 *
 * Queued and failure-tolerant by design: an editor saving a post must never see
 * an error because a search engine's endpoint is down. A missed ping costs
 * nothing beyond normal crawl latency.
 */
class SubmitToIndexNow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 30;

    /** @param list<string> $urls */
    public function __construct(public array $urls) {}

    public function handle(): void
    {
        $key = (string) config('services.indexnow.key');

        // No key, or a host crawlers cannot reach: nothing to do. Submitting
        // localhost URLs gets the domain rejected, not indexed.
        if ($key === '' || $this->urls === [] || AppUrl::isLocal()) {
            return;
        }

        $host = parse_url(AppUrl::current(), PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return;
        }

        // The API caps a batch at 10,000 URLs. This site has 18, but the cap is
        // cheap to respect and expensive to discover in production.
        $urls = array_slice(array_values(array_unique($this->urls)), 0, 10000);

        try {
            $response = Http::timeout(10)->post('https://api.indexnow.org/IndexNow', [
                'host' => $host,
                'key' => $key,
                'keyLocation' => rtrim(AppUrl::current(), '/')."/{$key}.txt",
                'urlList' => $urls,
            ]);

            // 200 accepted, 202 accepted-pending-key-validation. Anything else is
            // worth a log line: 403 means the key file is not being served, and
            // that fails silently forever otherwise.
            if (! in_array($response->status(), [200, 202], true)) {
                Log::warning('IndexNow rejected the submission', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'count' => count($urls),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('IndexNow submission failed', ['error' => $e->getMessage()]);
        }
    }
}
