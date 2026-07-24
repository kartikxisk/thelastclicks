<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Industry;
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

    public function show(Industry $industry): View
    {
        $industry->load(['media', 'mediaItems.media']);

        return view('industries.show', [
            'industry' => $industry,
        ]);
    }
}
