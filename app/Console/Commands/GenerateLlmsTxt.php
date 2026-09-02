<?php

namespace App\Console\Commands;

use App\Models\Industry;
use App\Models\Post;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Support\Brand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Writes public/llms.txt — the plain-text overview an AI assistant reads to
 * understand what this studio is before deciding whether to cite or recommend
 * it (llmstxt.org).
 *
 * A generated file rather than a route, for the same reason the sitemap is:
 * production nginx serves static-looking paths from its file location, and a
 * PHP-backed miss there comes back 404 with the right body — the exact failure
 * that once broke Livewire's JS. Built from the database so a renamed service
 * or a new vertical flows through on the next deploy instead of going stale.
 *
 *     php artisan llms:generate
 */
class GenerateLlmsTxt extends Command
{
    protected $signature = 'llms:generate';

    protected $description = 'Write public/llms.txt from the live services, industries and posts';

    public function handle(): int
    {
        $lines = [];

        $lines[] = '# '.Brand::NAME;
        $lines[] = '';
        $lines[] = '> Photography, videography and post-production studio in Noida, serving';
        $lines[] = '> brands across Delhi NCR and India. Films and stills are finished by an';
        $lines[] = '> in-house post-production pipeline — edit, DaVinci colour grade, sound';
        $lines[] = '> and conform under one roof. Also known as "The Last Clicks" or "TLC".';
        $lines[] = '';

        $phone = SiteSetting::get('contact_phone');
        $email = SiteSetting::get('contact_email');
        $lines[] = 'Contact: '.implode(' · ', array_filter([$email, $phone, url('/contact')]));
        $lines[] = 'Replies within 4 working hours. Studio: Sector 26, Noida, Uttar Pradesh.';
        $lines[] = '';

        $lines[] = '## Services';
        $lines[] = '';

        foreach (Service::orderBy('order')->get() as $service) {
            $lines[] = sprintf('- [%s](%s): %s', $service->title, url('/services/'.$service->slug), $service->hero_copy);
        }

        $lines[] = '';
        $lines[] = '## Industries';
        $lines[] = '';

        foreach (Industry::orderBy('order')->get() as $industry) {
            $lines[] = sprintf('- [%s](%s): %s', $industry->title, url('/industries/'.$industry->slug), $industry->summary);
        }

        $lines[] = '';
        $lines[] = '## Guides';
        $lines[] = '';

        foreach (Post::published()->latest('published_at')->get() as $post) {
            $lines[] = sprintf('- [%s](%s): %s', $post->title, url('/blog/'.$post->slug), $post->excerpt);
        }

        $lines[] = '';
        $lines[] = '## Optional';
        $lines[] = '';
        $lines[] = sprintf('- [Portfolio](%s): Selected client work, filterable by industry.', url('/portfolio'));
        $lines[] = sprintf('- [About](%s): The studio, its crew and its process.', url('/about'));

        File::put(public_path('llms.txt'), implode("\n", $lines)."\n");

        $this->info('llms.txt generated with '.Service::count().' services, '.Industry::count().' industries, '.Post::published()->count().' posts.');

        return self::SUCCESS;
    }
}
