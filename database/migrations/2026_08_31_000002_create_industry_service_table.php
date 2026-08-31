<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which industries a service page says it covers.
 *
 * The two axes existed with nothing joining them: a visitor reading the
 * videography page had no route to the wedding or alcobev work that proves it,
 * and the six industry pages were reachable from exactly one place on the site.
 *
 * Derivation was tried first and does not work. Service and Industry both link
 * to Work, so "industries this service has work under" looks like it needs no
 * schema — but the pivot rows are the studio's editorial claim about what it
 * offers, not a fact about which projects happen to be filed. A service with no
 * work attached yet would silently claim to cover nothing.
 *
 * Mirrors service_work and industry_work exactly, so all three read the same way
 * in the admin and in the models.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industry_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['industry_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industry_service');
    }
};
