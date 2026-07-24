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
            'works' => Work::published()
                ->with(['media', 'mediaItems.media'])
                ->orderBy('order')->orderByDesc('id')
                ->get(),
        ]);
    }
}
