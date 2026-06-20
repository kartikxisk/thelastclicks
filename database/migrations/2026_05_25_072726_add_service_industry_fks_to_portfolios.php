<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $t) {
            $t->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            $t->foreign('industry_id')->references('id')->on('industries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $t) {
            $t->dropForeign(['service_id']);
            $t->dropForeign(['industry_id']);
        });
    }
};
