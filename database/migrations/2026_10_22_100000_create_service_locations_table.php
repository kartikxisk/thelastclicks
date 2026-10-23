<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_locations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('service_id')->constrained()->cascadeOnDelete();
            $t->string('city_slug', 32);
            $t->string('city_name', 64);
            $t->string('headline');
            $t->text('intro');
            $t->json('sections');
            // Sidebar "At a glance" facts (list of short strings) and visible Q&A
            // (list of {q, a}). Nullable: a page without them renders without
            // the card/section rather than an empty shell.
            $t->json('highlights')->nullable();
            $t->json('faqs')->nullable();
            $t->string('meta_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->boolean('is_published')->default(true);
            $t->unsignedInteger('order')->default(0);
            $t->timestamps();

            // One page per service per city; also the key the seeder's
            // create-only firstOrCreate relies on.
            $t->unique(['service_id', 'city_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_locations');
    }
};
