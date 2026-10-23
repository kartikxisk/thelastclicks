<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceLocation;
use Illuminate\Database\Seeder;

class ServiceLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/service_locations.php');

        if (! is_file($file)) {
            return;
        }

        $services = Service::pluck('id', 'slug');

        // CREATE-ONLY, keyed on service + city. These pages are edited in the
        // admin after the first deploy; an updateOrCreate here would overwrite
        // an editor's rewrite on every deploy. Same reasoning as BillingSeeder:
        // bootstrap what is missing, never reconcile what exists.
        foreach (require $file as $i => $row) {
            $serviceId = $services[$row['service_slug']] ?? null;
            if ($serviceId === null) {
                continue;
            }

            ServiceLocation::firstOrCreate(
                ['service_id' => $serviceId, 'city_slug' => $row['city_slug']],
                [
                    'city_name' => $row['city_name'],
                    'headline' => $row['headline'],
                    'intro' => $row['intro'],
                    'sections' => $row['sections'],
                    'highlights' => $row['highlights'] ?? null,
                    'faqs' => $row['faqs'] ?? null,
                    'meta_title' => $row['meta_title'] ?? null,
                    'meta_description' => $row['meta_description'] ?? null,
                    'is_published' => true,
                    'order' => $i,
                ],
            );
        }
    }
}
