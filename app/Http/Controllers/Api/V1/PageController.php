<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ClientResource;
use App\Http\Resources\Api\V1\HeroSlideResource;
use App\Http\Resources\Api\V1\IndustryResource;
use App\Http\Resources\Api\V1\SeoResource;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Http\Resources\Api\V1\TestimonialResource;
use App\Http\Resources\Api\V1\WorkResource;
use App\Models\Client;
use App\Models\HeroSlide;
use App\Models\Industry;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Work;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

/**
 * Page-bundle endpoints: one response per frontend route, carrying exactly what
 * that route renders plus its metadata. Keeps composition in Laravel so the
 * frontend never fans out into six parallel requests to paint one screen.
 */
class PageController extends Controller
{
    public function home(): JsonResponse
    {
        return response()->json([
            'data' => [
                'hero_slides' => HeroSlideResource::collection(
                    HeroSlide::active()->with(HeroSlideResource::eagerLoads())->get()
                ),
                'services' => ServiceResource::collection(
                    Service::orderBy('order')->with(ServiceResource::eagerLoads())->get()
                ),
                'featured_works' => WorkResource::collection($this->featuredWorks()),
                'industries' => IndustryResource::collection(
                    Industry::orderBy('order')->orderBy('id')
                        ->with(IndustryResource::eagerLoads())->get()
                ),
                'testimonials' => TestimonialResource::collection(
                    Testimonial::published()->orderBy('order')->get()
                ),
                'clients' => ClientResource::collection(
                    Client::active()->orderBy('order')->with(ClientResource::eagerLoads())->get()
                ),
            ],
            'seo' => SeoResource::forPath('/', ['json_ld' => $this->siteJsonLd()]),
        ]);
    }

    /**
     * Featured works for the homepage strip; falls back to the most recent
     * published works so the section is never empty just because nobody
     * ticked "Show on homepage".
     *
     * Ported from HomeController::featuredWorks(). The homepage renders these
     * as a collage, which needs enough tiles to actually cluster — six leaves a
     * sparse row with nothing overlapping.
     *
     * @return Collection<int, Work>
     */
    protected function featuredWorks(): Collection
    {
        // One query rather than the Blade controller's featured-then-filler
        // pair. Sorting on is_featured first produces the identical ordering —
        // flagged works by (order, id desc), then the rest by (order, id desc)
        // — while saving three queries, since each extra works query drags its
        // own media and media_items eager loads along with it.
        return Work::published()
            ->with(WorkResource::eagerLoads())
            ->orderByDesc('is_featured')
            ->orderBy('order')
            ->orderByDesc('id')
            ->take(15)
            ->get();
    }

    /**
     * Sitewide structured data. Emitted on the homepage only — duplicating
     * Organization on every route adds bytes without adding signal.
     *
     * @return list<array<string, mixed>>
     */
    protected function siteJsonLd(): array
    {
        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => config('app.name'),
                'url' => url('/'),
                'logo' => url('/logo.png'),
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => config('app.name'),
                'url' => url('/'),
            ],
        ];
    }
}
