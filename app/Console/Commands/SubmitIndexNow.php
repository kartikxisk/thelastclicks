<?php

namespace App\Console\Commands;

use App\Jobs\SubmitToIndexNow;
use App\Support\AppUrl;
use Illuminate\Console\Command;

/**
 * Submits every URL in the generated sitemap to IndexNow.
 *
 * Reads public/sitemap.xml rather than rebuilding the URL list, so there is one
 * definition of "the pages this site has" and the two can never disagree. Run
 * after sitemap:generate on deploy.
 */
class SubmitIndexNow extends Command
{
    protected $signature = 'indexnow:submit {--dry-run : List the URLs without submitting}';

    protected $description = 'Submit the sitemap URLs to IndexNow (Bing, Yandex, Naver, Seznam)';

    public function handle(): int
    {
        if ((string) config('services.indexnow.key') === '') {
            $this->warn('INDEXNOW_KEY is not set — nothing submitted.');

            // Not a failure: the deploy script calls this unconditionally, and a
            // site that has not opted into IndexNow must not fail its deploy.
            return self::SUCCESS;
        }

        if (AppUrl::isLocal()) {
            $this->warn('APP_URL is local — refusing to submit. IndexNow rejects unreachable hosts.');

            return self::SUCCESS;
        }

        $path = public_path('sitemap.xml');

        if (! is_file($path)) {
            $this->error('public/sitemap.xml not found. Run sitemap:generate first.');

            return self::FAILURE;
        }

        $xml = @simplexml_load_file($path);

        if ($xml === false) {
            $this->error('public/sitemap.xml could not be parsed.');

            return self::FAILURE;
        }

        $urls = [];

        foreach ($xml->url ?? [] as $entry) {
            $loc = trim((string) $entry->loc);
            if ($loc !== '') {
                $urls[] = $loc;
            }
        }

        if ($urls === []) {
            $this->warn('Sitemap contains no URLs.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->line(implode(PHP_EOL, $urls));
            $this->info(count($urls).' URL(s) would be submitted.');

            return self::SUCCESS;
        }

        SubmitToIndexNow::dispatch($urls);

        $this->info(count($urls).' URL(s) queued for IndexNow submission.');

        return self::SUCCESS;
    }
}
