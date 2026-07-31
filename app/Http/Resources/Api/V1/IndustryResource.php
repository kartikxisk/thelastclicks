<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A vertical, with the quotes filed under it.
 *
 * `cover` goes through coverUrl(), which prefers the uploaded hero and then
 * image_url over gallery media — otherwise a YouTube poster in the media array
 * would hijack the card cover.
 *
 * @property-read Industry $resource
 */
class IndustryResource extends JsonResource
{
    public static $wrap = null;

    /** @return list<string> */
    public static function eagerLoads(): array
    {
        return ['media', 'mediaItems.media', 'testimonials'];
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $industry = $this->resource;

        return [
            'id' => $industry->id,
            'slug' => $industry->slug,
            'title' => $industry->title,
            'summary' => $industry->summary,
            'body' => $industry->body,
            'cover' => $industry->coverUrl(),
            'media' => $industry->mediaPayload(),
            // Resolved rather than left as a resource object: JsonResource
            // does not recurse, so an unresolved child would survive toArray()
            // as an object and only flatten at encode time — which breaks both
            // the contract snapshot and any caller that spreads this array.
            'testimonials' => TestimonialResource::collection($industry->testimonials)->resolve($request),
        ];
    }
}
