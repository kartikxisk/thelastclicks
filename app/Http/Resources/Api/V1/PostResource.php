<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A journal entry.
 *
 * @property-read Post $resource
 */
class PostResource extends JsonResource
{
    public static $wrap = null;

    /** Average adult reading speed, words per minute. */
    private const WORDS_PER_MINUTE = 200;

    /** @return list<string> */
    public static function eagerLoads(): array
    {
        return ['media', 'categories', 'tags'];
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $post = $this->resource;

        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'published_at' => $post->published_at?->toIso8601String(),
            'reading_minutes' => $this->readingMinutes(),
            'cover' => $post->getFirstMediaUrl('cover') ?: null,
            // The first category only. A post can carry several, but the card
            // design shows one — exposing the list as well would give the
            // frontend two sources of truth for the same badge.
            'category' => $this->option($post->categories->first()),
            'tags' => $post->tags->map(fn (Tag $tag) => $this->option($tag))->values(),
        ];
    }

    /** @return array{value: string, label: string}|null */
    private function option(Category|Tag|null $model): ?array
    {
        return $model ? ['value' => $model->slug, 'label' => $model->name] : null;
    }

    private function readingMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->resource->body));

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }
}
