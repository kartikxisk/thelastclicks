<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which projects a service page shows.
 *
 * The service pages could only show `gallery_urls` — loose image URLs carrying
 * no title, client or category, so the one page meant to sell a discipline made
 * its case with anonymous frames while the studio's actual projects sat in
 * `works`. Nothing linked the two: Work::CATEGORIES files a project by type
 * (brand-film, wedding, product), which is a different axis from the three
 * services, so no existing column could be reused.
 *
 * Many-to-many because a single shoot genuinely belongs under more than one
 * service — a campaign that produced both stills and a film is photography AND
 * videography, and forcing a choice would hide it from one of the two pages it
 * proves.
 *
 * No `order` column on the pivot: works already carry `order`, and a second
 * ordering axis would need a UI to manage and would silently disagree with the
 * portfolio's sequence.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_work', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Attaching the same project twice is a double-click, not an intent —
            // and it would render the tile twice.
            $table->unique(['service_id', 'work_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_work');
    }
};
