<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $t) {
            $col = $t->foreignId('work_category_id')->nullable()->after('industry_id');

            // SQLite can't DROP a column referenced by a foreign key, which the
            // later drop_work_categories migration needs to do — skip the FK there.
            if (DB::getDriverName() !== 'sqlite') {
                $col->constrained('work_categories')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $t) {
            $t->dropConstrainedForeignId('work_category_id');
        });
    }
};
