<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_service', function (Blueprint $table) {
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unique(['portfolio_id', 'service_id']);
        });

        // Backfill from the legacy single-service column (prod data).
        DB::table('portfolios')->whereNotNull('service_id')->orderBy('id')
            ->each(function (object $row) {
                DB::table('portfolio_service')->insertOrIgnore([
                    'portfolio_id' => $row->id,
                    'service_id' => $row->service_id,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_service');
    }
};
