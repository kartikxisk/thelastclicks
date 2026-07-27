<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-managed homepage hero backgrounds. Replaces a hardcoded CDN path to a
     * single pre-composited reel.
     *
     * There is no `type` column: whether a slide is a still or a film is a fact
     * about the uploaded file, and storing it separately just creates a second
     * source of truth that can disagree with the asset. HeroSlide::isVideo()
     * reads the media row's mime type instead.
     */
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
