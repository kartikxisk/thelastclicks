<?php

namespace App\Http\Resources\Api\V1;

use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * An ordered gallery row — image, uploaded video, or YouTube embed. Rows whose
 * file is missing or whose YouTube URL will not parse resolve to null and are
 * stripped by the collection, so the frontend never renders a hole.
 *
 * @property-read MediaItem $resource
 */
class MediaItemResource extends JsonResource
{
    public static $wrap = null;

    /** @return array<string, mixed>|null */
    public function toArray(Request $request): ?array
    {
        $item = $this->resource;
        $url = $item->resolvedUrl();

        if (! $url) {
            return null;
        }

        $file = $item->getFirstMedia('file');

        return [
            'type' => $item->type,
            'url' => $url,
            'poster' => $item->type === 'youtube' ? $item->thumbnailUrl() : null,
            'caption' => $item->caption,
            'width' => $file?->getCustomProperty('width'),
            'height' => $file?->getCustomProperty('height'),
            'mime' => $file?->mime_type,
        ];
    }

    /**
     * Strip rows that resolved to null. Without this override the collection
     * emits literal nulls into the JSON array, and the frontend has to guard
     * every map over it.
     *
     * @param  Collection<int, MediaItem>|array<int, MediaItem>  $resource
     */
    public static function collection($resource): AnonymousResourceCollection
    {
        $collection = parent::collection($resource);

        $collection->collection = $collection->collection
            ->reject(fn (self $item) => $item->resource->resolvedUrl() === null)
            ->values();

        return $collection;
    }
}
