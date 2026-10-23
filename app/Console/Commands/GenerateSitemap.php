<?php

namespace App\Console\Commands;

use App\Models\Industry;
use App\Models\Post;
use App\Models\SeoPage;
use App\Models\Service;
use App\Models\ServiceLocation;
use App\Models\Work;
use App\Support\AppUrl;
use App\Support\MediaUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        $add = function (string $path, $lastModified, ?string $image = null) use ($sitemap, $noindex): void {
            if (in_array(SeoPage::normalizePath($path), $noindex, true)) {
                return;
            }

            $url = Url::create(url($path));

            // lastmod is optional, so unknown means omitted. Stamping now() on
            // everything teaches crawlers that lastmod is meaningless, and they
            // then ignore it for the pages that really did change.
            if ($lastModified !== null) {
                $url->setLastModificationDate($lastModified);
            }

            // Absolute URLs only: a relative <image:loc> is invalid and can
            // get the whole sitemap entry rejected.
            $image = MediaUrl::resolve($image);
            if ($image !== null && preg_match('~^https?://~i', $image) === 1) {
                $url->addImage($image);
            }

            $sitemap->add($url);
        };

        // Newest of several nullable timestamps, or null when none is known.
        $newest = function (...$dates): ?Carbon {
            $dates = array_filter(array_map(
                fn ($d) => $d === null ? null : Carbon::parse($d),
                $dates,
            ));

            return $dates === [] ? null : max($dates);
        };

        $seoUpdated = SeoPage::query()->active()->pluck('updated_at', 'page_url')->all();

        $listing = [
            '/portfolio' => Work::published()->max('updated_at'),
            '/blog' => Post::published()->max('updated_at'),
            '/industries' => Industry::query()->max('updated_at'),
        ];

        $statics = [
            '/', '/about', '/blog', '/industries', '/portfolio',
            '/contact', '/privacy-policy', '/terms-of-service', '/cookie-policy', '/disclaimer',
        ];
        foreach ($statics as $path) {
            // Index pages change when their children do; the rest only when an
            // editor touches their SEO row. Neither has a date of its own.
            $add($path, $newest($listing[$path] ?? null, $seoUpdated[SeoPage::normalizePath($path)] ?? null));
        }

        // Pages render published works, so a new or edited work freshens its
        // service/industry page even though that row itself never changed.
        // One aggregate per relation instead of a query per row.
        $serviceWorks = DB::table('service_work')
            ->join('works', 'works.id', '=', 'service_work.work_id')
            ->where('works.is_published', true)
            ->selectRaw('service_work.service_id as owner_id, max(works.updated_at) as newest')
            ->groupBy('service_work.service_id')
            ->pluck('newest', 'owner_id');
        $industryWorks = DB::table('industry_work')
            ->join('works', 'works.id', '=', 'industry_work.work_id')
            ->where('works.is_published', true)
            ->selectRaw('industry_work.industry_id as owner_id, max(works.updated_at) as newest')
            ->groupBy('industry_work.industry_id')
            ->pluck('newest', 'owner_id');

        foreach (Service::with('media')->get() as $svc) {
            $add('/services/'.$svc->slug, $newest($svc->updated_at, $serviceWorks[$svc->id] ?? null), $svc->heroUrl());
        }
        // City pages render the service's works too, so lastmod follows the
        // newer of the location row and the service's works aggregate above.
        foreach (ServiceLocation::published()->with('service:id,slug')->orderBy('id')->get() as $loc) {
            $add('/services/'.$loc->service->slug.'/'.$loc->city_slug, $newest($loc->updated_at, $serviceWorks[$loc->service_id] ?? null));
        }
        foreach (Industry::with(['media', 'mediaItems.media'])->get() as $industry) {
            $add('/industries/'.$industry->slug, $newest($industry->updated_at, $industryWorks[$industry->id] ?? null), $industry->coverUrl());
        }
        foreach (Post::published()->with('media')->get() as $p) {
            $add('/blog/'.$p->slug, $p->updated_at, $p->getFirstMediaUrl('cover') ?: null);
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->info('sitemap.xml generated');

        return self::SUCCESS;
    }
}
