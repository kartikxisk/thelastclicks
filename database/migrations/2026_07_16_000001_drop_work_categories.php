<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Work-categories feature removed (admin resource, model and the
     * portfolio ?category deep links). Drops the column, the table and
     * the stale shield permissions.
     */
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            // SQLite (tests) can't drop foreign keys; dropColumn rebuilds the table there.
            if (DB::getDriverName() === 'sqlite') {
                $table->dropColumn('work_category_id');
            } else {
                $table->dropConstrainedForeignId('work_category_id');
            }
        });

        Schema::dropIfExists('work_categories');

        DB::table('permissions')
            ->where('name', 'like', '%\_work::category')
            ->delete();
    }

    public function down(): void
    {
        // Irreversible — the WorkCategory model and admin resource no longer exist.
    }
};
