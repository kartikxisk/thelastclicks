<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Our own billing entities.
 *
 * Every field here is either printed on an invoice or decides how one is
 * calculated — `address_state_code` in particular is the input to the
 * intra-state vs inter-state tax split, which is why it is a separate column
 * and not something parsed back out of the address.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();

            $table->boolean('is_gst_registered')->default(true);
            $table->string('gstin', 15)->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('cin', 21)->nullable();
            $table->string('lut_number')->nullable();
            $table->date('lut_valid_till')->nullable();

            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state')->nullable();
            $table->string('address_state_code', 2)->nullable();
            // Twelve, not six: the form advertises non-IN addresses (a country
            // other than IN makes the invoice an export, and the LUT fields
            // above exist only for exports), and a UK postcode like SW1A 1AA is
            // eight characters. At six, that address simply cannot be entered —
            // and on MySQL an over-long value is error 1406, not a truncation.
            $table->string('address_postal_code', 12)->nullable();
            $table->string('address_country', 2)->default('IN');

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc', 11)->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('upi_id')->nullable();

            // Capped at 5 because the longest number this produces —
            // PREFX/26-27/0001 — is exactly the 16 characters Rule 46(b) allows.
            $table->string('invoice_prefix', 5)->default('INV');
            $table->string('credit_note_prefix', 5)->default('CRN');
            $table->string('proforma_prefix', 5)->default('PRO');
            $table->string('receipt_prefix', 5)->default('RCT');

            $table->string('default_template')->default('classic');
            $table->string('default_currency', 3)->default('INR');
            $table->unsignedInteger('default_payment_terms_days')->default(7);
            $table->text('default_terms')->nullable();
            $table->text('default_notes')->nullable();
            $table->text('footer_note')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
