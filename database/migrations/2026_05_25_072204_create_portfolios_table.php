<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolios', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('service_id')->nullable();    // FK constrained in Task 8
            $t->foreignId('industry_id')->nullable();   // FK constrained in Task 8
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('client')->nullable();
            $t->unsignedSmallInteger('year')->nullable();
            $t->longText('body')->nullable();
            $t->enum('status', ['draft', 'published'])->default('draft');
            $t->timestamps();
            $t->index(['status', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
