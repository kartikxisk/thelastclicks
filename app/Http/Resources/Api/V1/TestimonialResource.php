<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A published client quote.
 *
 * No avatar field: Testimonial does not use InteractsWithMedia, so there is no
 * image to expose. Adding one would need a migration and an admin form change,
 * which is outside this API layer.
 *
 * @property-read Testimonial $resource
 */
class TestimonialResource extends JsonResource
{
    public static $wrap = null;

    /** @return list<string> */
    public static function eagerLoads(): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $testimonial = $this->resource;

        return [
            'id' => $testimonial->id,
            'quote' => $testimonial->quote,
            'client_name' => $testimonial->client_name,
            'role_company' => $testimonial->role_company,
        ];
    }
}
