<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A portfolio project.
 *
 * `cover`, `media`, `crafts` and `credits` come from the model's own accessors
 * rather than being rebuilt here — those encode rules the Blade grid already
 * depends on: the cover fallback chain, dropping half-filled credit rows, and
 * filtering craft slugs that no longer exist in the CRAFTS map.
 *
 * @property-read Work $resource
 */
class WorkResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Relations this resource reads. Controllers pass these to with().
     *
     * @return list<string>
     */
    public static function eagerLoads(): array
    {
        return ['media', 'mediaItems.media'];
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $work = $this->resource;

        return [
            'id' => $work->id,
            'slug' => $work->slug,
            'title' => $work->title,
            'summary' => $work->summary,
            'client' => $work->client,
            'category' => $work->category,
            'category_label' => $work->categoryLabel(),
            'crafts' => $work->craftLabels(),
            'credits' => $work->creditRows(),
            'location' => $work->location,
            'agency' => $work->agency,
            'year' => $work->year,
            'cover' => $work->coverUrl(),
            'preview_video_url' => $work->previewVideoUrl(),
            'media' => $work->mediaPayload(),
            'is_featured' => (bool) $work->is_featured,
        ];
    }
}
