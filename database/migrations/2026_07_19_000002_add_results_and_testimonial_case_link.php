<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->json('results')->nullable()->after('credits');
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->foreignId('portfolio_id')->nullable()->after('industry_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('portfolio_id');
        });
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn('results');
        });
    }
};
