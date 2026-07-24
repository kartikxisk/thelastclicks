<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lead lifecycle timestamps. Response time and win rate could be derived from
 * the activity log, but that is unindexable and breaks the moment a status is
 * changed outside the panel — dedicated columns keep the dashboard aggregates
 * cheap and sortable. Existing rows stay null and are excluded from averages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // First move off `new` — the moment someone actually responded.
            $table->timestamp('contacted_at')->nullable()->after('status')->index();
            // Moved to won or lost.
            $table->timestamp('closed_at')->nullable()->after('contacted_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex(['contacted_at']);
            $table->dropIndex(['closed_at']);
            $table->dropColumn(['contacted_at', 'closed_at']);
        });
    }
};
