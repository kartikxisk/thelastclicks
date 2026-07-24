<?php

namespace App\Console\Commands;

use App\Models\Industry;
use App\Models\Post;
use App\Models\SeoPage;
use App\Models\Service;
use App\Support\AppUrl;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--force : Generate even when APP_URL looks non-public}';

    protected $description = 'Generate public/sitemap.xml';

    public function handle(): int
    {
        // Every <loc> is built from APP_URL. Generating against a local URL would bake
        // localhost links into a file crawlers read, so refuse unless explicitly forced.
        $base = AppUrl::current();

        if (! $this->option('force') && AppUrl::isLocal($base)) {
            $this->error("Refusing to generate — APP_URL is {$base}.");
            $this->line('Run this on production, or pass --force if you really want local URLs.');

            return self::FAILURE;
        }

        $sitemap = Sitemap::create();

        // Pages marked noindex in Manage SEO must not be advertised in the sitemap —
        // listing a page we're telling crawlers to skip is a contradictory signal.
        $noindex = SeoPage::query()
            ->active()
            ->where('noindex', true)
            ->pluck('page_url')
            ->all();

        $add = function (string $path, $lastModified) use ($sitemap, $noindex): void {
            if (in_array(SeoPage::normalizePath($path), $noindex, true)) {
                return;
            }

            $sitemap->add(Url::create(url($path))->setLastModificationDate($lastModified));
        };

        $statics = [
            '/', '/about', '/blog', '/industries', '/our-works',
            '/contact', '/privacy-policy', '/terms-of-service', '/cookie-policy', '/disclaimer',
        ];
        foreach ($statics as $path) {
            $add($path, now());
        }

        foreach (Service::all() as $svc) {
            $add('/services/'.$svc->slug, $svc->updated_at);
        }
        foreach (Industry::all() as $ind) {
            $add('/industries/'.$ind->slug, $ind->updated_at);
        }
        foreach (Post::published()->get() as $p) {
            $add('/blog/'.$p->slug, $p->updated_at);
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->info('sitemap.xml generated');

        return self::SUCCESS;
    }
}
