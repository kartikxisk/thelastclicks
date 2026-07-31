<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\SeoResource;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Http\Resources\Api\V1\WorkResource;
use App\Models\Service;
use App\Models\Work;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /** Enough tiles to fill the strip without turning it into a second grid. */
    private const RELATED_WORKS = 6;

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => ServiceResource::collection(
                Service::orderBy('order')->with(ServiceResource::eagerLoads())->get()
            ),
            'seo' => SeoResource::forPath('/services'),
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $service = Service::where('slug', $slug)
            ->with(ServiceResource::eagerLoads())
            ->firstOrFail();

        $data = ServiceResource::make($service)->resolve($request);
        $data['related_works'] = WorkResource::collection($this->relatedWorks())->resolve($request);

        return response()->json([
            'data' => $data,
            'seo' => SeoResource::forPath("/services/{$slug}", [
                'canonical' => url("/services/{$slug}"),
                'json_ld' => [[
                    '@context' => 'https://schema.org',
                    '@type' => 'Service',
                    'name' => $service->title,
                    'description' => $service->hero_copy,
                    'provider' => ['@type' => 'Organization', 'name' => config('app.name')],
                    'url' => url("/services/{$slug}"),
                ]],
            ]),
        ]);
    }

    /**
     * Work to show under a service.
     *
     * Recent published work rather than work filed against this service: Work
     * carries no service relation, so there is nothing to join on. Showing the
     * newest six is honest and keeps the section from ever being empty.
     *
     * @return Collection<int, Work>
     */
    protected function relatedWorks(): Collection
    {
        return Work::published()
            ->with(WorkResource::eagerLoads())
            ->orderBy('order')->orderByDesc('id')
            ->take(self::RELATED_WORKS)
            ->get();
    }
}
