<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\IndustryResource;
use App\Http\Resources\Api\V1\SeoResource;
use App\Models\Industry;
use Illuminate\Http\JsonResponse;

/**
 * Index only.
 *
 * Industry detail pages were retired: /industries/{slug} 301s to the index and
 * clicking a card opens a pre-filled quote instead. Do not add a show() method
 * without also reversing that redirect.
 */
class IndustryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => IndustryResource::collection(
                Industry::orderBy('order')->orderBy('id')
                    ->with(IndustryResource::eagerLoads())
                    ->get()
            ),
            'seo' => SeoResource::forPath('/industries', ['canonical' => url('/industries')]),
        ]);
    }
}
