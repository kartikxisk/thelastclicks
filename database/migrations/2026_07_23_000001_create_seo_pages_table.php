<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Per-URL SEO overrides managed from the admin panel ("Manage SEO").
// One row per exact page path; the row wins over whatever the page sets.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_url')->unique();   // normalized path: '/', '/about', '/blog/my-post'
            $table->string('label')->nullable();    // admin-only nickname

            $table->string('title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image_url')->nullable();   // pasted URL — wins over upload
            $table->string('og_image_path')->nullable();  // uploaded file on the media disk

            $table->string('canonical_url')->nullable();
            $table->boolean('noindex')->default(false);
            $table->boolean('nofollow')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_pages');
    }
};
