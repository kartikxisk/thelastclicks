<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\MediaItem;
use App\Support\MediaSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Re-attaches industry imagery to a rebuilt database.
 *
 * IndustriesSeeder recreates the industry rows, but a cover uploaded through the
 * admin — and the ordered media items behind the homepage artist reel — are
 * medialibrary rows it knows nothing about. Production therefore came up with
 * every industry present, every cover silently falling back to the seeded stock
 * path, and the artist band missing entirely, while the files sat untouched on
 * S3. The same gap ServiceMediaSeeder was written to close, on the other model.
 *
 * Rows are replayed under their original ids, which is what makes the URL
 * resolve to the object already in the bucket rather than needing a re-upload.
 * See MediaSnapshot for why the id is load-bearing.
 *
 * Refresh the fixture with `php artisan app:export-industry-media`.
 */
class IndustryMediaSeeder extends Seeder
{
    /** Overridable so a test can point at its own fixture. */
    public function __construct(public ?string $fixturePath = null) {}

    public function run(): void
    {
        $path = $this->fixturePath ?? database_path('seeders/data/industry-media.json');

        if (! File::exists($path)) {
            $this->command?->warn('No industry media fixture — run `php artisan app:export-industry-media`.');

            return;
        }

        $rows = json_decode((string) File::get($path), true);

        if (! is_array($rows)) {
            $this->command?->error('industry-media.json is not valid JSON.');

            return;
        }

        $covers = 0;
        $items = 0;

        foreach ($rows as $row) {
            $slug = $row['slug'] ?? null;

            if (! is_string($slug)) {
                continue;
            }

            $industry = Industry::firstWhere('slug', $slug);

            // An industry the seeder no longer creates (a retired slug still in
            // an old fixture) simply has nothing to attach to.
            if (! $industry) {
                continue;
            }

            $covers += MediaSnapshot::restore($industry, $row['media'] ?? []);

            // Clear the wreckage of a restore that half-succeeded before the
            // items are replayed, so the repair lands on a clean slate rather
            // than stacking a second reel beside the broken one.
            $this->pruneOrphanItems($industry);

            foreach ($row['media_items'] ?? [] as $itemRow) {
                $items += $this->restoreItem($industry, $itemRow);
            }
        }

        $this->command?->info("Restored {$covers} industry cover row(s) and {$items} media item(s).");
    }

    /**
     * Delete upload-backed items that have no upload behind them.
     *
     * An image or video row exists to carry a file; with none it renders
     * nothing and is filtered out of every gallery, so it can only be the
     * residue of a restore whose media rows were skipped — which is exactly
     * what production hit when the fixture ids collided with client logos.
     * A youtube row is deliberately excluded: it carries a URL, never a file.
     */
    protected function pruneOrphanItems(Industry $industry): void
    {
        $industry->mediaItems()
            ->whereIn('type', ['image', 'video'])
            ->whereDoesntHave('media')
            ->get()
            ->each->delete();
    }

    /**
     * @param  array<string, mixed>  $itemRow
     * @return int how many media rows were created for it
     */
    protected function restoreItem(Industry $industry, array $itemRow): int
    {
        $media = $itemRow['media'] ?? [];
        $firstId = is_array($media) && isset($media[0]['id']) ? $media[0]['id'] : null;
        $attributes = $itemRow['attributes'] ?? [];

        // A youtube row carries no file, so there is no media id to match on.
        // Its URL is the identity instead — without this the row is recreated
        // on every deploy and the gallery grows a duplicate each time.
        if ($firstId === null) {
            $url = $attributes['youtube_url'] ?? null;

            if (is_string($url) && $url !== '' && $industry->mediaItems()
                ->where('youtube_url', $url)->exists()) {
                return 0;
            }
        }

        // Matched on the media id rather than on the item's own: media ids are
        // what the fixture pins and what the storage path is built from, so an
        // item already holding this file is the same item however its row was
        // numbered. Without this a re-run would stack duplicate frames into the
        // reel on every deploy.
        if (is_int($firstId) && MediaItem::query()
            ->where('mediable_type', $industry->getMorphClass())
            ->where('mediable_id', $industry->getKey())
            ->whereHas('media', fn ($q) => $q->whereKey($firstId))
            ->exists()) {
            return 0;
        }

        /** @var MediaItem $item */
        $item = $industry->mediaItems()->create($attributes);

        return MediaSnapshot::restore($item, is_array($media) ? $media : []);
    }
}
