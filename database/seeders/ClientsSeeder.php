<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The client logo strip. Canonical list of display name, website, and the S3
 * key of the logo already uploaded to the shared media bucket. Because local and
 * production point at the same bucket, seeding these keys makes the strip render
 * on a fresh `db:seed` without re-uploading — the object already exists. Only the
 * key is stored; Client::logoUrl() prepends the CloudFront host from config at
 * runtime. A later admin upload (Spatie media) transparently overrides the key.
 */
class ClientsSeeder extends Seeder
{
    /**
     * logo file key => [display name, website, media-disk S3 key]
     *
     * @var array<string, array{0: string, 1: ?string, 2: ?string}>
     */
    public const CLIENTS = [
        'dlf' => ['DLF', 'https://www.dlf.in', '27/dlf.png'],
        'amazon' => ['Amazon', 'https://www.amazon.in', '23/amazon.png'],
        'adobe' => ['Adobe', 'https://www.adobe.com', '22/adobe.png'],
        'meta' => ['Meta', 'https://about.meta.com', '31/meta.png'],
        'taskus' => ['TaskUs', 'https://www.taskus.com', '38/taskus.png'],
        'wns' => ['WNS', 'https://www.wns.com', '39/wns.png'],
        'mothercare' => ['Mothercare', 'https://www.mothercare.in', '32/mothercare.png'],
        'oberoi' => ['Oberoi Hotels', 'https://www.oberoihotels.com', '33/oberoi.png'],
        'taj-hotels' => ['Taj Hotels', 'https://www.tajhotels.com', '37/taj-hotels.png'],
        'hyatt' => ['Hyatt', 'https://www.hyatt.com', '28/hyatt.png'],
        'ritz-carlton' => ['Ritz-Carlton', 'https://www.ritzcarlton.com', '35/ritz-carlton.png'],
        'bmw' => ['BMW', 'https://www.bmw.in', '26/bmw.png'],
        'mercedes-benz' => ['Mercedes-Benz', 'https://www.mercedes-benz.co.in', '30/mercedes-benz.png'],
        'range-rover' => ['Range Rover', 'https://www.landrover.in', '34/range-rover.png'],
        'rolls-royce' => ['Rolls-Royce', 'https://www.rolls-roycemotorcars.com', '36/rolls-royce.png'],
        'johnnie-walker' => ['Johnnie Walker', 'https://www.johnniewalker.com', '29/johnnie-walker.png'],
        'bacardi' => ['Bacardi', 'https://www.bacardi.com', '24/bacardi.png'],
        'beluga' => ['Beluga', 'https://belugavodka.com', '25/beluga.png'],
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

        foreach (self::CLIENTS as [$name, $url, $logo]) {
            Client::updateOrCreate(
                ['name' => $name],
                [
                    'url' => $url,
                    'logo_path' => $logo,
                    'order' => $order++,
                    'is_active' => true,
                ],
            );
        }
    }
}
