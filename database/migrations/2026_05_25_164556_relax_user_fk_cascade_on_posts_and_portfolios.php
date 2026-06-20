<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            $t->dropForeign(['author_id']);
            $t->foreignId('author_id')->nullable()->change();
            $t->foreign('author_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('portfolios', function (Blueprint $t) {
            $t->dropForeign(['owner_id']);
            $t->foreignId('owner_id')->nullable()->change();
            $t->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            $t->dropForeign(['author_id']);
            // NOTE: column stays nullable on down — restoring NOT NULL would fail if any nulls exist
            $t->foreign('author_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('portfolios', function (Blueprint $t) {
            $t->dropForeign(['owner_id']);
            $t->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
