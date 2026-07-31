<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreQuoteRequest;
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
    /**
     * Static pages whose slug is a fixed route, not a database lookup. Using
     * the public URL slug directly means there is no alias table to keep in
     * sync between here and the frontend router.
     *
     * @var list<string>
     */
    public const STATIC_PAGES = [
        'privacy-policy', 'terms-of-service', 'cookie-policy', 'disclaimer', 'thank-you',
    ];

    /**
     * Static pages that carry body copy. thank-you is absent on purpose: it is
     * a designed confirmation screen, not an article, so the frontend owns its
     * markup and only needs the metadata.
     *
     * @var list<string>
     */
    private const LEGAL_PAGES = [
        'privacy-policy', 'terms-of-service', 'cookie-policy', 'disclaimer',
    ];

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

    public function about(): JsonResponse
    {
        return response()->json([
            'data' => [
                'testimonials' => TestimonialResource::collection(
                    Testimonial::published()->orderBy('order')->get()
                ),
                'clients' => ClientResource::collection(
                    Client::active()->orderBy('order')->with(ClientResource::eagerLoads())->get()
                ),
                'stats' => [
                    'works' => Work::published()->count(),
                    'clients' => Client::active()->count(),
                ],
            ],
            'seo' => SeoResource::forPath('/about'),
        ]);
    }

    public function contact(): JsonResponse
    {
        return response()->json([
            'data' => [
                'services' => ServiceResource::collection(
                    Service::orderBy('order')->with(ServiceResource::eagerLoads())->get()
                ),
                'project_types' => collect(Work::CATEGORIES)
                    ->map(fn (string $label, string $slug) => ['value' => $slug, 'label' => $label])
                    ->values(),
                // Value and label are the same string: the quote form stores
                // the label itself, so an API that invented slugs here would
                // save values the admin's existing rows do not use.
                'budget_ranges' => collect(StoreQuoteRequest::BUDGET_RANGES)
                    ->map(fn (string $range) => ['value' => $range, 'label' => $range])
                    ->values(),
            ],
            'seo' => SeoResource::forPath('/contact'),
        ]);
    }

    public function staticPage(string $slug): JsonResponse
    {
        return response()->json([
            'data' => [
                // Rendered from a partial holding only the copy, never from the
                // full page view — that would drag the layout, nav, footer and
                // script tags into the response. The same partial backs the
                // Blade page, so there is one source of truth until Plan 3
                // moves this copy into the admin.
                'body' => in_array($slug, self::LEGAL_PAGES, true)
                    ? view("pages.legal.{$slug}")->render()
                    : null,
            ],
            'seo' => SeoResource::forPath("/{$slug}", ['canonical' => url("/{$slug}")]),
        ]);
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
