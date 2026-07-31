<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\PostResource;
use App\Http\Resources\Api\V1\SeoResource;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    private const PER_PAGE = 9;

    /** Enough to fill the strip under an article without becoming a second index. */
    private const RELATED = 3;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:64'],
            'tag' => ['nullable', 'string', 'max:64'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Post::published()->with(PostResource::eagerLoads());

        if (filled($validated['category'] ?? null)) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $validated['category']));
        }

        if (filled($validated['tag'] ?? null)) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $validated['tag']));
        }

        $posts = $query->latest('published_at')->paginate(self::PER_PAGE)->withQueryString();
        $page = (int) ($validated['page'] ?? 1);

        return response()->json([
            'data' => PostResource::collection($posts->items()),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
            'filters' => [
                'categories' => Category::orderBy('name')->get(['slug', 'name'])
                    ->map(fn (Category $c) => ['value' => $c->slug, 'label' => $c->name])->values(),
                'tags' => Tag::orderBy('name')->get(['slug', 'name'])
                    ->map(fn (Tag $t) => ['value' => $t->slug, 'label' => $t->name])->values(),
            ],
            'seo' => SeoResource::forPath('/blog', [
                'canonical' => $page > 1 ? url('/blog').'?page='.$page : url('/blog'),
            ]),
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $post = Post::published()->where('slug', $slug)
            ->with(PostResource::eagerLoads())
            ->firstOrFail();

        $data = PostResource::make($post)->resolve($request);
        $data['related'] = PostResource::collection(
            Post::published()->where('id', '!=', $post->id)
                ->with(PostResource::eagerLoads())
                ->latest('published_at')
                ->take(self::RELATED)
                ->get()
        )->resolve($request);

        return response()->json([
            'data' => $data,
            'seo' => SeoResource::forPath("/blog/{$slug}", [
                // The post's own title wins: there is no SeoPage row per post,
                // and falling back to the row for /blog would give every
                // article the index's title.
                'title' => $post->seo_title ?: $post->title,
                'description' => $post->seo_description ?: $post->excerpt,
                'canonical' => url("/blog/{$slug}"),
                'json_ld' => [[
                    '@context' => 'https://schema.org',
                    '@type' => 'BlogPosting',
                    'headline' => $post->title,
                    'datePublished' => $post->published_at?->toIso8601String(),
                    'dateModified' => $post->updated_at?->toIso8601String(),
                    'image' => $post->getFirstMediaUrl('cover') ?: null,
                    'author' => ['@type' => 'Organization', 'name' => config('app.name')],
                    'mainEntityOfPage' => url("/blog/{$slug}"),
                ]],
            ]),
        ]);
    }
}
