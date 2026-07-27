<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fields the work grid needs to say anything beyond title + client.
     *
     *   category           one discipline per project — the grid's primary filter axis
     *   crafts             in-house crafts on this project (edit/colour/sound/vfx).
     *                      Filterable, and the only on-site proof of the post-production USP.
     *   credits            named roles: [{role, name}] — director, DOP, editor, colourist
     *   location / agency  the metadata the reference-class sites all carry
     *   preview_video_url  short muted loop for the grid tile, distinct from the full
     *                      film in media_items — a hero cut is too heavy to autoplay in a grid
     */
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->string('category')->nullable()->after('client');
            $table->json('crafts')->nullable()->after('category');
            $table->json('credits')->nullable()->after('crafts');
            $table->string('location')->nullable()->after('credits');
            $table->string('agency')->nullable()->after('location');
            $table->string('preview_video_url')->nullable()->after('agency');

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn([
                'category', 'crafts', 'credits', 'location', 'agency', 'preview_video_url',
            ]);
        });
    }
};
