<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\SeoResource;
use App\Http\Resources\Api\V1\WorkResource;
use App\Models\Work;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The portfolio grid's data source.
 *
 * Filters are derived from what is actually present rather than from the
 * CATEGORIES constant, so a category nobody has filed work under never shows
 * up as a filter chip that returns nothing.
 *
 * There is no industry filter: Work carries no industry relation. The
 * portfolio_service pivot belonged to the retired Portfolio feature and does
 * not apply here.
 */
class WorkController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:64'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Work::published()->with(WorkResource::eagerLoads());

        if (filled($validated['category'] ?? null)) {
            $query->where('category', $validated['category']);
        }

        $works = $query->orderBy('order')->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $page = (int) ($validated['page'] ?? 1);

        return response()->json([
            'data' => WorkResource::collection($works->items()),
            'meta' => [
                'current_page' => $works->currentPage(),
                'last_page' => $works->lastPage(),
                'per_page' => $works->perPage(),
                'total' => $works->total(),
            ],
            'filters' => [
                'categories' => $this->availableCategories(),
            ],
            'seo' => SeoResource::forPath('/portfolio', [
                'canonical' => $page > 1 ? url('/portfolio').'?page='.$page : url('/portfolio'),
            ]),
        ]);
    }

    /**
     * Categories at least one published work actually uses, labelled from the
     * CATEGORIES map and ordered as that map declares them so the filter row
     * does not reshuffle when content changes.
     *
     * @return list<array{value: string, label: string}>
     */
    protected function availableCategories(): array
    {
        $used = Work::published()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->all();

        return collect(Work::CATEGORIES)
            ->filter(fn (string $label, string $slug) => in_array($slug, $used, true))
            ->map(fn (string $label, string $slug) => ['value' => $slug, 'label' => $label])
            ->values()
            ->all();
    }
}
