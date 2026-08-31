<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IndustryController extends Controller
{
    public function index(): View
    {
        return view('industries.index', [
            'industries' => Industry::orderBy('order')->orderBy('id')
                ->with(['media', 'mediaItems.media'])
                ->get(),
        ]);
    }

    /**
     * Slugs the earlier eight-vertical taxonomy used. They are indexed, so they
     * 301 to the deck rather than 404. Checked only after a live lookup misses,
     * which is what stops a reused slug from being shadowed by its own redirect.
     *
     * @var list<string>
     */
    private const RETIRED_SLUGS = [
        // The eight-vertical taxonomy.
        'automobile-luxury', 'brands-agencies', 'corporate-enterprise', 'fashion-creators',
        'lifestyle-beverage', 'nightlife-entertainment', 'spaces-interiors', 'weddings-celebrations',
        // Retired before that, and still listed in IndustriesSeeder::$retiredSlugs.
        'corporate-events', 'brands-products', 'motion-post-production', 'motion-graphics',
    ];

    public function show(string $slug): View|RedirectResponse
    {
        $industry = Industry::where('slug', $slug)->first();

        if (! $industry) {
            if (in_array($slug, self::RETIRED_SLUGS, true)) {
                return redirect('/industries', 301);
            }

            abort(404);
        }

        return view('industries.show', [
            'industry' => $industry,
            // Eager-loaded here rather than in the view: the grid reads cover
            // media per tile, and resolving that lazily is the N+1 that
            // assertQueryCount() in tests/Pest.php exists to catch.
            'works' => $industry->publishedWorks()->with(['media', 'mediaItems.media'])->get(),
        ]);
    }
}
