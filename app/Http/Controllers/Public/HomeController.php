<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'services' => Service::orderBy('order')->get(),
            'portfolio' => Portfolio::published()->latest()->take(6)->get(),
            'testimonials' => Testimonial::published()->orderBy('order')->get(),
        ]);
    }
}
