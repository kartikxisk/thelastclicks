<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceLocation;
use Illuminate\View\View;

class ServiceLocationController extends Controller
{
    public function show(string $slug, string $city): View
    {
        $service = Service::where('slug', $slug)->firstOrFail();

        // 404 for unpublished too: a draft city page must not be reachable by URL.
        $location = $service->locations()->published()->where('city_slug', $city)->firstOrFail();

        // media eager-loaded: the grid reads cover media per tile (N+1 otherwise).
        $works = $service->publishedWorks()->with('media')->get();

        $otherCities = $service->locations()->published()
            ->where('id', '!=', $location->id)->get(['id', 'service_id', 'city_slug', 'city_name']);

        $otherServices = ServiceLocation::published()
            ->where('city_slug', $city)
            ->where('service_id', '!=', $service->id)
            ->with('service:id,slug,title')
            ->orderBy('order')->orderBy('id')
            ->get();

        return view('services.location', compact('service', 'location', 'works', 'otherCities', 'otherServices'));
    }
}
