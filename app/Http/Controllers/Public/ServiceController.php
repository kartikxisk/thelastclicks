<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function show(string $slug): View
    {
        $service = Service::where('slug', $slug)->firstOrFail();

        return view('services.show', compact('service'));
    }
}
