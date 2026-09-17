<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The saved rate card an invoice line is built from.
 *
 * `rate_paise` is an integer for the reason every money column here is: the GST
 * split is a three-way division and a float leaves the parts and the total a
 * paisa apart. `tax_rate_bps` is basis points for the same reason — a 2.5%
 * half-rate is the integer 250, where 2.5 as a float is not itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();

            // Null means shared: most of the rate card applies whichever entity
            // issues the invoice.
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sac_code', 8)->nullable()->default('998383');
            $table->string('unit')->default('project');
            $table->bigInteger('rate_paise')->default(0);
            $table->unsignedInteger('tax_rate_bps')->default(1800);
            $table->boolean('is_expense')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};
