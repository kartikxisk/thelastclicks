<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crew/talent feature removed entirely (public pages retired earlier,
     * admin resource deleted). Drops the table and purges rows that
     * referenced the model polymorphically.
     */
    public function up(): void
    {
        DB::table('media')->where('model_type', 'App\Models\Crew')->delete();
        DB::table('permissions')
            ->where('name', 'like', '%\_crew')
            ->delete();

        Schema::dropIfExists('crew');
    }

    public function down(): void
    {
        // Irreversible — the Crew model and admin resource no longer exist.
    }
};
