<?php

use App\Models\Industry;
use App\Models\Post;
use App\Models\SeoPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('generates a sitemap.xml file with public urls', function () {
    Artisan::call('sitemap:generate', ['--force' => true]);

    $path = public_path('sitemap.xml');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)
        ->toContain('<urlset')
        ->toContain('<loc>'.url('/').'</loc>')
        ->toContain('<loc>'.url('/contact').'</loc>')
        ->toContain('<loc>'.url('/services/photography').'</loc>')
        // Industry detail pages are live again, so they belong in the sitemap.
        ->toContain('<loc>'.url('/industries/'.Industry::first()->slug).'</loc>')
        ->toContain('<loc>'.url('/blog').'</loc>');

    @unlink($path);
});

it('does not stamp generation time on static pages', function () {
    Artisan::call('sitemap:generate', ['--force' => true]);
    $content = file_get_contents(public_path('sitemap.xml'));
    @unlink(public_path('sitemap.xml'));

    $xml = simplexml_load_string($content);
    $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    $nodes = $xml->xpath('//s:url[s:loc="'.url('/disclaimer').'"]');

    expect($nodes)->toHaveCount(1);
    // No SeoPage row for this path → nothing honest to say, so no lastmod.
    if (! SeoPage::where('page_url', '/disclaimer')->exists()) {
        expect(isset($nodes[0]->lastmod))->toBeFalse();
    }
});

it('excludes unpublished posts and gives a post with a cover an image entry', function () {
    $cover = Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);
    $draft = Post::factory()->create(['status' => 'draft', 'published_at' => null]);
    $file = UploadedFile::fake()->image('c.jpg');
    $cover->addMedia($file->getRealPath())
        ->preservingOriginal()->toMediaCollection('cover');

    Artisan::call('sitemap:generate', ['--force' => true]);
    $content = file_get_contents(public_path('sitemap.xml'));
    @unlink(public_path('sitemap.xml'));

    expect($content)
        ->toContain('<loc>'.url('/blog/'.$cover->slug).'</loc>')
        ->not->toContain('/blog/'.$draft->slug)
        ->toContain('<image:loc>');
});
