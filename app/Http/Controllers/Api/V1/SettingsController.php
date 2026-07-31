<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

/**
 * Global chrome the frontend's root layout needs on every route. Deliberately
 * flat — this is configuration, not content, and it has no SEO of its own.
 *
 * Reads go through SiteSetting's accessors rather than raw get() wherever one
 * exists: those own the fallback and validation rules the Blade site already
 * depends on (an unrecognised tile ratio falls back, an absent brand logo stays
 * null rather than substituting a bundled file).
 */
class SettingsController extends Controller
{
    /**
     * Every platform the admin form can write. Kept as a fixed list so unset
     * platforms serialise as null rather than vanishing — the frontend maps a
     * known key set instead of guarding each one.
     *
     * @var list<string>
     */
    private const SOCIAL_PLATFORMS = [
        'instagram', 'youtube', 'facebook', 'linkedin', 'x', 'behance', 'pinterest',
    ];

    /** Leaves an already-absolute URL alone; resolves a public/ path against APP_URL. */
    private function absolute(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
            ? $url
            : url($url);
    }

    public function __invoke(): JsonResponse
    {
        $socials = SiteSetting::get('socials');
        $socials = is_array($socials) ? $socials : [];

        return response()->json([
            'data' => [
                'contact_email' => SiteSetting::get('contact_email', config('mail.from.address')),
                'contact_phone' => SiteSetting::get('contact_phone'),
                'whatsapp_url' => SiteSetting::get('whatsapp_url') ?: null,
                'socials' => collect(self::SOCIAL_PLATFORMS)
                    ->mapWithKeys(fn (string $key) => [$key => ($socials[$key] ?? null) ?: null])
                    ->all(),
                'brand_logo_url' => SiteSetting::brandLogoUrl(),
                'favicon_url' => SiteSetting::faviconUrl(),
                // Absolute, always. ctaVideoUrl() falls back to the bundled
                // /videos/bg-footer.mp4, which is a path under Laravel's
                // public/ — and the Next frontend is served from the same
                // origin but a different process, so a relative path 404s
                // there. An uploaded video already resolves to an absolute
                // CDN URL and passes through untouched.
                'cta_video_url' => $this->absolute(SiteSetting::ctaVideoUrl()),
                'work_tile_ratio' => SiteSetting::workTileRatio(),
                'seo_defaults' => [
                    'title' => SiteSetting::get('seo_default_title') ?: null,
                    'description' => SiteSetting::get('seo_default_description') ?: null,
                    'og_image' => SiteSetting::get('seo_default_og_image') ?: null,
                ],
            ],
        ]);
    }
}
