<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function privacy(): View
    {
        return view('pages.privacy-policy');
    }

    public function terms(): View
    {
        return view('pages.terms-of-service');
    }

    public function cookies(): View
    {
        return view('pages.cookie-policy');
    }

    public function disclaimer(): View
    {
        return view('pages.disclaimer');
    }

    public function thankYou(): View
    {
        return view('pages.thank-you');
    }
}
