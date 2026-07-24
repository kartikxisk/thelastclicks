<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which pipeline stage a note was written at. Reading it off the quote's current
 * status would be wrong the moment the lead moves on, so it is captured at write
 * time and never changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_notes', function (Blueprint $table) {
            $table->string('stage')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('quote_notes', function (Blueprint $table) {
            $table->dropColumn('stage');
        });
    }
};
