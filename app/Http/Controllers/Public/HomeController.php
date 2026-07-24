<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Work;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'services' => Service::orderBy('order')->with('media')->get(),
            'industries' => Industry::orderBy('order')->orderBy('id')->with(['media', 'mediaItems.media'])->get(),
            'testimonials' => Testimonial::published()->orderBy('order')->get(),
            'featuredWorks' => $this->featuredWorks(),
        ]);
    }

    /**
     * Featured works for the homepage strip; falls back to the most recent
     * published works so the section is never empty just because nobody
     * ticked "Show on homepage".
     *
     * @return Collection<int, Work>
     */
    protected function featuredWorks(): Collection
    {
        $base = fn () => Work::published()->with(['media', 'mediaItems.media']);

        $featured = $base()->where('is_featured', true)
            ->orderBy('order')->orderByDesc('id')->take(6)->get();

        return $featured->isNotEmpty()
            ? $featured
            : $base()->orderBy('order')->orderByDesc('id')->take(6)->get();
    }
}
