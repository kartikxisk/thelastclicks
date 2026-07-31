<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * The single media shape every V1 endpoint emits. Carries enough for both
 * `next/image` (url, srcset, intrinsic dimensions) and a WebGL texture
 * (absolute url, mime) so the frontend never needs a second request to
 * discover what it just received.
 *
 * @property-read Media|null $resource
 */
class MediaResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Wrap a media record that may be absent.
     *
     * JsonResource::resolve() casts its payload with `(array)`, so a resource
     * wrapping null serialises as `[]`, never as null — which would reach the
     * frontend as an empty object on a field typed `Media | null`. Every
     * nullable media field goes through here rather than through make().
     */
    public static function nullable(?Media $media): ?self
    {
        return $media instanceof Media ? new self($media) : null;
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $media = $this->resource;

        return [
            'url' => $media->getFullUrl(),
            'srcset' => $media->getSrcset() ?: null,
            'width' => $media->getCustomProperty('width'),
            'height' => $media->getCustomProperty('height'),
            'mime' => $media->mime_type,
            'alt' => $media->getCustomProperty('alt') ?? $media->name,
        ];
    }
}
