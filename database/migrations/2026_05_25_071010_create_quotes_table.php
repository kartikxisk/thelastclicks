<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('company')->nullable();
            $t->string('email');
            $t->string('phone')->nullable();
            $t->string('project_type')->nullable();
            $t->string('budget')->nullable();
            $t->string('timeline')->nullable();
            $t->text('message')->nullable();
            $t->string('source_page')->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('ua', 512)->nullable();
            $t->enum('status', ['new', 'contacted', 'qualified', 'won', 'lost'])->default('new');
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
