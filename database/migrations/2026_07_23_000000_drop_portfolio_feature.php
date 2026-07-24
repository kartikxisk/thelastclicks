<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Portfolio feature retired — drop its tables and the testimonials FK that
// pointed at it. Ordered so foreign keys are gone before their target table.
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('portfolio_service');

        if (Schema::hasColumn('testimonials', 'portfolio_id')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->dropConstrainedForeignId('portfolio_id');
            });
        }

        Schema::dropIfExists('portfolios');
    }

    public function down(): void
    {
        // Portfolio feature permanently removed — no rollback.
    }
};
