<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        $featured = Portfolio::published()->latest()->first();
        $itemsByYear = Portfolio::published()
            ->with(['service', 'industry', 'workCategory'])
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->orderBy('year', 'desc')
            ->latest()
            ->get()
            ->groupBy('year');

        return view('portfolio.index', [
            'featured' => $featured,
            'itemsByYear' => $itemsByYear,
            'services' => Service::orderBy('order')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $item = Portfolio::published()->where('slug', $slug)->firstOrFail();

        return view('portfolio.show', compact('item'));
    }
}
