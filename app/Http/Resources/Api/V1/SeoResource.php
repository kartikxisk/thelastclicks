<?php

namespace App\Http\Resources\Api\V1;

use App\Models\SeoPage;

/**
 * Metadata for one route, assembled from the admin-managed SeoPage row plus
 * per-model overrides. Not a JsonResource — it is a plain array factory,
 * because every endpoint embeds it rather than returning it alone.
 *
 * The frontend spreads this straight into Next's `generateMetadata`, so any
 * key added here must be one Next can consume without transformation.
 */
class SeoResource
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function forPath(string $path, array $overrides = []): array
    {
        $path = '/'.ltrim($path, '/');

        $row = SeoPage::query()
            ->where('is_active', true)
            ->where('page_url', $path)
            ->first();

        $base = [
            'title' => $row?->title,
            'description' => $row?->meta_description,
            'canonical' => $row?->canonical_url ?: url($path),
            'noindex' => (bool) $row?->noindex,
            'nofollow' => (bool) $row?->nofollow,
            'og' => [
                'title' => $row?->og_title ?: $row?->title,
                'description' => $row?->og_description ?: $row?->meta_description,
                'image' => $row?->og_image_url ?: ($row?->og_image_path ? url($row->og_image_path) : null),
            ],
            'json_ld' => [],
        ];

        // Overrides win, but merge into `og` rather than replacing it wholesale
        // so a caller supplying only an og image does not wipe the row's title.
        if (isset($overrides['og'])) {
            $base['og'] = [...$base['og'], ...$overrides['og']];
            unset($overrides['og']);
        }

        return [...$base, ...$overrides];
    }
}
