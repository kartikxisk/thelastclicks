<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Work;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function index(): View
    {
        return view('works.index', [
            // `industries` is eager-loaded because both the chip row and every
            // tile read it — without this it is one query per project, which on
            // a fifty-tile grid is the whole page's query budget.
            'works' => Work::published()
                ->with(['media', 'mediaItems.media', 'industries'])
                ->orderBy('order')->orderByDesc('id')
                ->get(),
        ]);
    }
}
