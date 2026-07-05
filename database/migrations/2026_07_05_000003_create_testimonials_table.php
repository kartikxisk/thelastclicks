<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('industry_id')->nullable()->constrained()->nullOnDelete();
            $t->text('quote');
            $t->string('client_name');
            $t->string('role_company')->nullable();
            $t->unsignedSmallInteger('order')->default(0);
            $t->boolean('is_published')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
