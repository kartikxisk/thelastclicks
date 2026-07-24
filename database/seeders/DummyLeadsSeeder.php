<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Dev-only sample leads spread across the pipeline and the last 30 days, so the
 * lead desk and kanban have something to render.
 *
 * Deliberately NOT wired into DatabaseSeeder — production must never invent
 * enquiries, and the test suite keeps its clean-slate baseline. Run on demand:
 *   php artisan db:seed --class=DummyLeadsSeeder
 */
class DummyLeadsSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe previous samples only, leaving anything real that came in via the site.
        Quote::query()->where('source_page', '/dummy-seed')->get()->each->delete();

        $owners = User::query()->role(['Super-admin', 'Sales'])->pluck('id')->all();

        // [name, company, status, days ago, hours ago, budget]
        $rows = [
            ['Ritika Malhotra', 'Aurora Beverages', 'new', 0, 1, '₹15L – ₹50L'],
            ['Sameer Kapoor', 'Vanguard Group', 'new', 0, 9, '₹50L+'],
            ['Neha Iyer', 'Loom Studio', 'new', 1, 2, '₹5L – ₹15L'],
            ['Arjun Mehta', null, 'contacted', 2, 0, 'Under ₹5L'],
            ['Priya Nair', 'Helios Motors', 'contacted', 4, 0, '₹15L – ₹50L'],
            ['Kabir Shah', 'Nocturne Live', 'qualified', 7, 0, '₹15L – ₹50L'],
            ['Ananya Rao', 'Meridian Hotels', 'qualified', 11, 0, '₹50L+'],
            ['Devansh Gupta', 'Lumen Interiors', 'won', 16, 0, '₹15L – ₹50L'],
            ['Farah Sheikh', 'Atlas Realty', 'won', 21, 0, '₹50L+'],
            ['Rohan Desai', 'Peak Fitness', 'lost', 26, 0, 'Under ₹5L'],
        ];

        foreach ($rows as $i => [$name, $company, $status, $days, $hours, $budget]) {
            $createdAt = now()->subDays($days)->subHours($hours);

            $quote = Quote::factory()->create([
                'name' => $name,
                'company' => $company,
                'status' => 'new',
                'budget' => $budget,
                'source_page' => '/dummy-seed',
                'assigned_to' => $owners === [] ? null : $owners[$i % count($owners)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Move through the pipeline so QuoteObserver stamps realistic
            // contacted_at / closed_at rather than back-filling them by hand.
            if ($status !== 'new') {
                $this->advance($quote, $status, $createdAt);
            }
        }
    }

    /**
     * Walk the lead up the real pipeline instead of jumping straight to its end
     * stage, so the seeded timeline shows a plausible history and the data obeys
     * the same hierarchy the UI enforces.
     */
    protected function advance(Quote $quote, string $target, Carbon $createdAt): void
    {
        $comments = [
            'contacted' => 'Called and introduced the studio.',
            'qualified' => 'Budget and dates confirmed — sending a treatment.',
            'won' => 'Signed. Shoot dates locked in.',
            'lost' => 'Went with an in-house team this time.',
        ];

        foreach ($this->pathTo($target) as $stage) {
            $quote->transitionTo($stage, $comments[$stage] ?? null);
        }

        // Back-date the lifecycle stamps the observer set to "now", so response
        // times and win-rate windows on the dashboard stay believable.
        $responded = $createdAt->copy()->addHours(random_int(1, 6));
        $quote->contacted_at = $responded;
        $quote->closed_at = $quote->isClosed()
            ? $responded->copy()->addDays(random_int(2, 9))
            : null;
        $quote->saveQuietly();
    }

    /**
     * The legal sequence of stages from `new` up to the target.
     *
     * @return list<string>
     */
    protected function pathTo(string $target): array
    {
        return match ($target) {
            'contacted' => ['contacted'],
            'qualified' => ['contacted', 'qualified'],
            'won' => ['contacted', 'qualified', 'won'],
            'lost' => ['contacted', 'qualified', 'lost'],
            default => [],
        };
    }
}
