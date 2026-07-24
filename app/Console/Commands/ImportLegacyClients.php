<?php

namespace App\Console\Commands;

use App\Models\Client;
use Database\Seeders\ClientsSeeder;
use Illuminate\Console\Command;

class ImportLegacyClients extends Command
{
    protected $signature = 'clients:import-legacy';

    protected $description = 'Upload logos from public/clients/ to the media disk and attach them to the seeded Client records';

    public function handle(): int
    {
        $files = glob(public_path('clients/*.{svg,png,webp,jpg,jpeg,SVG,PNG,WEBP,JPG,JPEG}'), GLOB_BRACE) ?: [];

        if ($files === []) {
            $this->warn('Nothing to import — public/clients/ is empty.');

            return self::SUCCESS;
        }

        $order = (int) Client::max('order');

        foreach ($files as $path) {
            // Same mapping the seeder uses, so a logo lands on its seeded row
            // instead of creating a second "Bmw" next to "BMW".
            $name = ClientsSeeder::nameForFile($path);

            $client = Client::firstOrCreate(
                ['name' => $name],
                ['order' => ++$order, 'is_active' => true],
            );

            if ($client->getFirstMedia('logo')) {
                $this->line("skip    {$name} — already has a logo");

                continue;
            }

            // preservingOriginal keeps public/clients/ intact as the legacy fallback.
            $client->addMedia($path)->preservingOriginal()->toMediaCollection('logo');
            $this->info("imported {$name}");
        }

        $this->newLine();
        $this->info('Done. Manage them under Site → Client logos.');

        return self::SUCCESS;
    }
}
