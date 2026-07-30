<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A service page's full content.
 *
 * Every array-cast column defaults to [] rather than null: the frontend
 * iterates these directly, and a null would force a guard at each of eight
 * call sites.
 *
 * @property-read Service $resource
 */
class ServiceResource extends JsonResource
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
        $service = $this->resource;

        return [
            'id' => $service->id,
            'slug' => $service->slug,
            'title' => $service->title,
            'hero_headline' => $service->hero_headline,
            'hero_copy' => $service->hero_copy,
            'hero_meta' => $service->hero_meta ?? [],
            // Resolved inline — JsonResource does not recurse, so an
            // unresolved child survives toArray() as an object.
            'hero' => MediaResource::nullable($service->getFirstMedia('hero'))?->resolve($request),
            'proof' => $service->proof ?? [],
            'pillars' => $service->pillars ?? [],
            'phases' => $service->phases ?? [],
            'kit' => $service->kit ?? [],
            'faqs' => $service->faqs ?? [],
            'cta' => $service->cta ?? [],
            'tags' => $service->tags ?? [],
            'gallery' => $service->gallery_urls ?? [],
            'body' => $service->body,
            'share' => $service->share,
        ];
    }
}
