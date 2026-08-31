<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which industry a project was shot for.
 *
 * The portfolio could be filtered by `Work::CATEGORIES` (what the piece is — a
 * brand film, a wedding, a product shoot) and by `CRAFTS` (what was done in
 * house). Neither answers the question a visitor actually arrives with, which is
 * who the work was for. Industries already exist as their own records with their
 * own titles and order; nothing connected them to the projects that prove them.
 *
 * Many-to-many because a single shoot genuinely serves more than one: a launch
 * film for a car brand's hospitality arm belongs under both, and forcing a
 * choice would hide it from one of the two filters it answers.
 *
 * Mirrors `service_work` deliberately — same shape, same constraints — because
 * the admin edits both from the same form and a second, subtly different pivot
 * would be a trap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industry_work', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Attaching the same project twice is a double-click, not an intent —
            // and it would render the chip's count wrong and the tile twice.
            $table->unique(['industry_id', 'work_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industry_work');
    }
};
