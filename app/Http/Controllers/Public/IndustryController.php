<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Models\Portfolio;
use Illuminate\View\View;

class IndustryController extends Controller
{
    public function index(): View
    {
        return view('industries.index', [
            'industries' => Industry::orderBy('order')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $industry = Industry::where('slug', $slug)->firstOrFail();
        $work = Portfolio::published()->where('industry_id', $industry->id)->latest()->take(12)->get();
        $categories = $industry->workCategories;
        $testimonials = $industry->testimonials()->where('is_published', true)->get();

        return view('industries.show', compact('industry', 'work', 'categories', 'testimonials'));
    }
}
