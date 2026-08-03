<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-URL SEO override, managed in the admin panel.
 *
 * Matching is on the exact normalized path, so '/', '/about' and
 * '/blog/my-post' are all addressable. The layout resolves one row per
 * request and lets it win over whatever the page itself passed.
 */
class SeoPage extends Model
{
    protected $fillable = [
        'page_url', 'label',
        'title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description', 'og_image_url', 'og_image_path',
        'canonical_url', 'noindex', 'nofollow', 'is_active',
    ];

    protected $casts = [
        'noindex' => 'boolean',
        'nofollow' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** @param Builder<SeoPage> $q
     * @return Builder<SeoPage>
     */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /**
     * '/about/', 'about?x=1' and 'https://site.test/about' all become '/about'.
     * Root stays '/'.
     */
    public static function normalizePath(?string $path): string
    {
        $path = parse_url((string) $path, PHP_URL_PATH) ?: '/';
        $trimmed = trim($path, '/');

        return $trimmed === '' ? '/' : '/'.$trimmed;
    }

    /** Always store the normalized form so lookups can't miss on a stray slash. */
    public function setPageUrlAttribute(?string $value): void
    {
        $this->attributes['page_url'] = static::normalizePath($value);
    }

    /** Active override for a path (defaults to the current request). */
    public static function forPath(?string $path = null): ?self
    {
        return static::query()
            ->active()
            ->where('page_url', static::normalizePath($path ?? request()->path()))
            ->first();
    }

    /** Pasted URL wins; otherwise resolve the uploaded file on the media disk. */
    public function ogImage(): ?string
    {
        return MediaUrl::resolve($this->og_image_url)
            ?? MediaUrl::onMediaDisk($this->og_image_path);
    }

    /** Null when nothing is restricted — no tag is better than 'index,follow'. */
    public function robotsContent(): ?string
    {
        if (! $this->noindex && ! $this->nofollow) {
            return null;
        }

        return implode(',', [
            $this->noindex ? 'noindex' : 'index',
            $this->nofollow ? 'nofollow' : 'follow',
        ]);
    }
}
