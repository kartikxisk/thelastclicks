<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Crew;
use Illuminate\View\View;

class CrewController extends Controller
{
    public function index(): View
    {
        return view('crew.index', ['members' => Crew::orderBy('order')->get()]);
    }

    public function show(string $slug): View
    {
        $member = Crew::where('slug', $slug)->firstOrFail();

        return view('crew.show', compact('member'));
    }
}
