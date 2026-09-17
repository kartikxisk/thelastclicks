<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The party we bill — deliberately not the `clients` table.
 *
 * `clients` is the public-site logo wall: it carries an order and an active
 * flag and is read on every page render. Bolting a GSTIN and a billing address
 * onto it would widen those queries and leave every showcase row half-empty,
 * so the two stay separate with an optional link between them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_clients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();

            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('gstin', 15)->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('email')->nullable();
            $table->json('cc_emails')->nullable();
            $table->string('phone')->nullable();

            $table->string('billing_address_line1')->nullable();
            $table->string('billing_address_line2')->nullable();
            $table->string('billing_address_city')->nullable();
            $table->string('billing_address_state')->nullable();
            $table->string('billing_address_state_code', 2)->nullable();
            // Twelve, not six — see companies.address_postal_code. A client
            // billed outside India is the case this table's own helper text
            // describes ("Anything other than IN makes the invoice an export"),
            // and a UK postcode is eight characters.
            $table->string('billing_address_postal_code', 12)->nullable();
            $table->string('billing_address_country', 2)->default('IN');

            $table->string('place_of_supply_state_code', 2)->nullable();
            $table->unsignedInteger('payment_terms_days')->nullable();
            $table->string('currency', 3)->default('INR');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_clients');
    }
};
