<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The client logo strip. This is the canonical list: the display name and site
 * for each logo file that used to be hardcoded in public/clients/.
 *
 * Rows only — the logo images are uploaded to the media disk (S3) by
 * `php artisan clients:import-legacy`, which matches rows through this same
 * mapping. Keeping the upload out of the seeder means `db:seed` never has to
 * reach S3 (and the test suite stays offline and fast).
 */
class ClientsSeeder extends Seeder
{
    /**
     * logo filename (without extension) => [display name, website]
     *
     * @var array<string, array{0: string, 1: ?string}>
     */
    public const CLIENTS = [
        'dlf' => ['DLF', 'https://www.dlf.in'],
        'amazon' => ['Amazon', 'https://www.amazon.in'],
        'adobe' => ['Adobe', 'https://www.adobe.com'],
        'meta' => ['Meta', 'https://about.meta.com'],
        'taskus' => ['TaskUs', 'https://www.taskus.com'],
        'wns' => ['WNS', 'https://www.wns.com'],
        'mothercare' => ['Mothercare', 'https://www.mothercare.in'],
        'oberoi' => ['Oberoi Hotels', 'https://www.oberoihotels.com'],
        'taj-hotels' => ['Taj Hotels', 'https://www.tajhotels.com'],
        'hyatt' => ['Hyatt', 'https://www.hyatt.com'],
        'ritz-carlton' => ['Ritz-Carlton', 'https://www.ritzcarlton.com'],
        'bmw' => ['BMW', 'https://www.bmw.in'],
        'mercedes-benz' => ['Mercedes-Benz', 'https://www.mercedes-benz.co.in'],
        'range-rover' => ['Range Rover', 'https://www.landrover.in'],
        'rolls-royce' => ['Rolls-Royce', 'https://www.rolls-roycemotorcars.com'],
        'johnnie-walker' => ['Johnnie Walker', 'https://www.johnniewalker.com'],
        'bacardi' => ['Bacardi', 'https://www.bacardi.com'],
        'beluga' => ['Beluga', 'https://belugavodka.com'],
    ];

    /**
     * Display name for a logo file, so the seeder and the import command agree
     * on which row a file belongs to and never create duplicates.
     */
    public static function nameForFile(string $filename): string
    {
        $key = pathinfo($filename, PATHINFO_FILENAME);

        return self::CLIENTS[$key][0]
            ?? Str::of($key)->replace(['-', '_'], ' ')->title()->value();
    }

    public function run(): void
    {
        $order = 0;

        // Keys still name the intended logo file, but nothing reads them now that
        // artwork is uploaded rather than seeded.
        foreach (self::CLIENTS as [$name, $url]) {
            Client::updateOrCreate(
                ['name' => $name],
                [
                    'url' => $url,
                    // No seeded artwork — logos are uploaded through the admin.
                    // Until then the marquee falls back to styled wordmarks.
                    'logo_path' => null,
                    'order' => $order++,
                    'is_active' => true,
                ],
            );
        }
    }
}
