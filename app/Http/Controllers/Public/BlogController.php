<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $featured = Post::published()->latest('published_at')->first();
        $posts = Post::published()
            ->with('categories')
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->latest('published_at')
            ->paginate(12);

        return view('blog.index', [
            'featured' => $featured,
            'posts' => $posts,
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::published()->where('slug', $slug)->with(['author', 'categories', 'tags'])->firstOrFail();

        return view('blog.show', compact('post'));
    }
}
