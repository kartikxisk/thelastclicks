<?php

namespace App\Http\Resources\Api\V1;

use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One hero slide — an image or a video, with an optional poster.
 *
 * `asset` and `poster` are full MediaResources rather than bare URLs because
 * slide one is the LCP element: the frontend needs intrinsic dimensions to
 * reserve space, and the mime type to decide between <img> and a WebGL
 * VideoTexture before anything loads.
 *
 * `is_video` is derived here rather than left to the frontend, so the
 * video-or-image decision has one definition shared with the Blade site.
 *
 * @property-read HeroSlide $resource
 */
class HeroSlideResource extends JsonResource
{
    public static $wrap = null;

    /** @return list<string> */
    public static function eagerLoads(): array
    {
        return ['media'];
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $slide = $this->resource;
        $asset = $slide->getFirstMedia('asset');

        return [
            'id' => $slide->id,
            'label' => $slide->label,
            // Resolved inline — JsonResource does not recurse, so an
            // unresolved child survives toArray() as an object.
            'asset' => MediaResource::nullable($asset)?->resolve($request),
            'poster' => MediaResource::nullable($slide->getFirstMedia('poster'))?->resolve($request),
            'mime' => $asset?->mime_type,
            'is_video' => $slide->isVideo(),
        ];
    }
}
