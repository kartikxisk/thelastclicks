<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function show(string $slug): View
    {
        $service = Service::where('slug', $slug)->firstOrFail();
        $work = Portfolio::published()->where('service_id', $service->id)->latest()->take(6)->get();

        return view('services.show', compact('service', 'work'));
    }
}
