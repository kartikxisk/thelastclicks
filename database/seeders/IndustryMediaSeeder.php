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

        // Clear duplicates an earlier deploy already stacked up, keeping the
        // first of each file. The id-keyed dedupe this replaces let every
        // deploy add another copy of every frame, so the repair has to undo
        // what shipped as well as stop it happening again.
        $industry->mediaItems()
            ->with('media')
            ->get()
            ->groupBy(fn (MediaItem $item) => (string) $item->getMedia('file')->first()?->file_name)
            ->each(function ($group, $file) {
                if ($file === '' || $group->count() < 2) {
                    return;
                }

                $group->sortBy('id')->skip(1)->each->delete();
            });
    }

    /**
     * @param  array<string, mixed>  $itemRow
     * @return int how many media rows were created for it
     */
    protected function restoreItem(Industry $industry, array $itemRow): int
    {
        $media = $itemRow['media'] ?? [];
        $firstFile = is_array($media) && isset($media[0]['file_name'])
            ? (string) $media[0]['file_name']
            : null;
        $attributes = $itemRow['attributes'] ?? [];

        // A youtube row carries no file, so there is nothing to match on there.
        // Its URL is the identity instead — without this the row is recreated
        // on every deploy and the gallery grows a duplicate each time.
        if ($firstFile === null) {
            $url = $attributes['youtube_url'] ?? null;

            if (is_string($url) && $url !== '' && $industry->mediaItems()
                ->where('youtube_url', $url)->exists()) {
                return 0;
            }
        }

        // Matched on the file name, not the media id. The id looks like the
        // stronger key — it is what the fixture pins and what the storage path
        // is built from — but MediaSnapshot deliberately changes it when the
        // fixture's id is already taken. Keying on the id therefore stopped
        // recognising rows this seeder had itself restored, and every deploy
        // stacked a second copy of every frame into the reel: production
        // rendered fourteen strips instead of seven. The file name survives the
        // copy, so it identifies the frame across environments and re-runs.
        if (MediaItem::query()
            ->where('mediable_type', $industry->getMorphClass())
            ->where('mediable_id', $industry->getKey())
            ->whereHas('media', fn ($q) => $q->where('file_name', $firstFile))
            ->exists()) {
            return 0;
        }

        /** @var MediaItem $item */
        $item = $industry->mediaItems()->create($attributes);

        return MediaSnapshot::restore($item, is_array($media) ? $media : []);
    }
}
