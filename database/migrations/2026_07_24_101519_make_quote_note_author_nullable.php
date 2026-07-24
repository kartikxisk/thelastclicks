<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Not every comment has a human behind it — a stage change recorded by a job or
 * a seeder has no authenticated user, and the timeline already renders an
 * authorless note fine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('author_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('quote_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('author_id')->nullable(false)->change();
        });
    }
};
