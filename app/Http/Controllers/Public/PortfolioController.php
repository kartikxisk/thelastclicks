<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        $featured = Portfolio::published()->latest()->first();
        $itemsByYear = Portfolio::published()
            ->with(['service', 'industry', 'media'])
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->orderBy('year', 'desc')
            ->latest()
            ->get()
            ->groupBy('year');

        $all = Portfolio::published()->get();

        return view('portfolio.index', [
            'featured' => $featured,
            'itemsByYear' => $itemsByYear,
            'stats' => [
                'films' => $all->count(),
                'clients' => $all->pluck('client')->filter()->unique()->count(),
                'industries' => $all->pluck('industry_id')->filter()->unique()->count(),
                'yearMin' => $all->min('year'),
                'yearMax' => $all->max('year'),
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $item = Portfolio::published()->where('slug', $slug)->firstOrFail();

        return view('portfolio.show', compact('item'));
    }
}
