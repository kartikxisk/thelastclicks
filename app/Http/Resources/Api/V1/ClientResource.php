<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A client logo for the marquee.
 *
 * `logo` is a resolved URL string rather than a MediaResource: logoUrl()
 * falls back from an uploaded file to the admin-set logo_path, which may be an
 * S3 key or an absolute URL and has no Media record behind it.
 *
 * @property-read Client $resource
 */
class ClientResource extends JsonResource
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
        $client = $this->resource;

        return [
            'id' => $client->id,
            'name' => $client->name,
            'logo' => $client->logoUrl(),
            'url' => $client->url,
        ];
    }
}
