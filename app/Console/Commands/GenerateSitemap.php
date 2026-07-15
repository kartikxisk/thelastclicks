<?php

namespace App\Console\Commands;

use App\Models\Industry;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate public/sitemap.xml';

    public function handle(): int
    {
        $sitemap = Sitemap::create();

        $statics = [
            '/', '/about', '/our-process', '/portfolio', '/blog', '/industries',
            '/contact', '/privacy-policy', '/terms-of-service', '/cookie-policy', '/disclaimer',
        ];
        foreach ($statics as $path) {
            $sitemap->add(Url::create(url($path))->setLastModificationDate(now()));
        }

        foreach (Service::all() as $svc) {
            $sitemap->add(Url::create(url('/services/'.$svc->slug))->setLastModificationDate($svc->updated_at));
        }
        foreach (Industry::all() as $ind) {
            $sitemap->add(Url::create(url('/industries/'.$ind->slug))->setLastModificationDate($ind->updated_at));
        }
        foreach (Portfolio::published()->get() as $p) {
            $sitemap->add(Url::create(url('/portfolio/'.$p->slug))->setLastModificationDate($p->updated_at));
        }
        foreach (Post::published()->get() as $p) {
            $sitemap->add(Url::create(url('/blog/'.$p->slug))->setLastModificationDate($p->updated_at));
        }
        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->info('sitemap.xml generated');

        return self::SUCCESS;
    }
}
