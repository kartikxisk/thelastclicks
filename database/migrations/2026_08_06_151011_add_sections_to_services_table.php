<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-service headings for the Flow / Arsenal / Protocols blocks.
 *
 * These used to be hardcoded in services/show.blade.php, which held while every
 * service said "From brief to delivery" — but the pages diverged: videography
 * runs brief -> delivery, photography moodboard -> master, post ingest -> export.
 * One template string cannot be three headings, so the copy moves onto the model
 * where the rest of the page's content already lives.
 *
 * Nullable, and the template keeps the old strings as its fallback, so a service
 * with nothing here renders exactly as it did before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->json('sections')->nullable()->after('faqs');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('sections');
        });
    }
};
