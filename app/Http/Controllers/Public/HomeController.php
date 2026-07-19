<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        /** @var array<int, array<string, string>> $homeStripRaw */
        $homeStripRaw = SiteSetting::get('home_strip', []);
        /** @var array<int, string> $heroVideosRaw */
        $heroVideosRaw = SiteSetting::get('hero_videos', []);

        $stripSettings = collect($homeStripRaw);
        $heroSlugs = collect($heroVideosRaw);

        $portfolios = Portfolio::published()
            ->whereIn('slug', $stripSettings->pluck('portfolio_slug')->merge($heroSlugs)->unique())
            ->with('media')
            ->get()
            ->keyBy('slug');

        return view('home', [
            'services' => Service::orderBy('order')->with('media')->get(),
            'testimonials' => Testimonial::published()->orderBy('order')->get(),
            'stripCards' => $this->stripCards($stripSettings, $portfolios),
            'heroVideos' => $this->heroVideos($heroSlugs, $portfolios),
        ]);
    }

    /**
     * @param  Collection<int, array<string, string>>  $settings
     * @param  Collection<string, Portfolio>  $portfolios
     * @return list<array<string, string>>
     */
    protected function stripCards(Collection $settings, Collection $portfolios): array
    {
        return $settings
            ->map(function (array $entry) use ($portfolios): ?array {
                $portfolio = $portfolios->get($entry['portfolio_slug'] ?? '');
                $videoUrl = $portfolio?->getFirstMediaUrl('gallery');

                if (! $videoUrl) {
                    return null;
                }

                return [
                    'video_url' => $videoUrl,
                    'poster_url' => $portfolio->getFirstMediaUrl('cover'),
                    'tag' => $entry['tag'] ?? '',
                    'title' => $entry['title'] ?? '',
                    'meta' => $entry['meta'] ?? '',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, string>  $slugs
     * @param  Collection<string, Portfolio>  $portfolios
     * @return list<array<string, string>>
     */
    protected function heroVideos(Collection $slugs, Collection $portfolios): array
    {
        return $slugs
            ->map(function (string $slug) use ($portfolios): ?array {
                $portfolio = $portfolios->get($slug);
                $videoUrl = $portfolio?->getFirstMediaUrl('gallery');

                if (! $videoUrl) {
                    return null;
                }

                return [
                    'video_url' => $videoUrl,
                    'poster_url' => $portfolio->getFirstMediaUrl('cover'),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
