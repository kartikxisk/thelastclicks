<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $t) {
            $t->foreignId('work_category_id')->nullable()->after('industry_id')
                ->constrained('work_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $t) {
            $t->dropConstrainedForeignId('work_category_id');
        });
    }
};
