<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the billing feature's storage: companies, billing clients and the
 * rate card.
 *
 * The create migrations are deleted rather than kept alongside this one, but a
 * deployed environment has already run them, so the tables are dropped forward
 * rather than by rewinding history. dropIfExists covers the environment that
 * never ran the originals.
 *
 * service_items goes first: its company_id foreign key points at companies,
 * and MySQL refuses to drop a parent while a child references it.
 */
return new class extends Migration
{
    /** Shield's permission suffixes for the three removed resources. */
    private const PERMISSION_SUFFIXES = ['company', 'billing::client', 'service::item'];

    /** The morph class the removed Company model wrote into shared tables. */
    private const COMPANY_MORPH = 'App\\Models\\Company';

    public function up(): void
    {
        Schema::dropIfExists('service_items');
        Schema::dropIfExists('billing_clients');
        Schema::dropIfExists('companies');

        // shield:generate only adds, so without this the twenty-odd billing
        // permissions linger as unassignable checkboxes in the role editor.
        if (Schema::hasTable('permissions')) {
            $stale = DB::table('permissions')
                // Unescaped `_` on purpose: `\_` needs an ESCAPE clause on
                // SQLite, where it silently matched nothing. As a wildcard it
                // is harmless — no other permission ends in these suffixes.
                ->where(function ($q) {
                    foreach (self::PERMISSION_SUFFIXES as $suffix) {
                        $q->orWhere('name', 'like', '%_'.$suffix);
                    }
                })
                ->pluck('id');

            if ($stale->isNotEmpty()) {
                foreach (['role_has_permissions', 'model_has_permissions'] as $pivot) {
                    if (Schema::hasTable($pivot)) {
                        DB::table($pivot)->whereIn('permission_id', $stale)->delete();
                    }
                }

                DB::table('permissions')->whereIn('id', $stale)->delete();
            }
        }

        // Accounts existed only to hold the billing permissions. Left behind it
        // is an empty role the role editor still offers, and canAccessPanel()
        // no longer admits it.
        if (Schema::hasTable('roles')) {
            $accounts = DB::table('roles')->where('name', 'Accounts')->pluck('id');

            if ($accounts->isNotEmpty()) {
                foreach (['role_has_permissions', 'model_has_roles'] as $pivot) {
                    if (Schema::hasTable($pivot)) {
                        DB::table($pivot)->whereIn('role_id', $accounts)->delete();
                    }
                }

                DB::table('roles')->whereIn('id', $accounts)->delete();
            }
        }

        // Rows that pointed at a Company in shared tables. The media rows go so
        // nothing resolves a model class that no longer exists; the FILES do
        // not — the logo stays on MEDIA_DISK and the signature and stamp on
        // the billing_private disk until someone removes them by hand. The
        // activity rows go because they are the history of the bank block and
        // the UPI ID, which is exactly the data this removal is meant to drop.
        if (Schema::hasTable('media')) {
            DB::table('media')->where('model_type', self::COMPANY_MORPH)->delete();
        }
        if (Schema::hasTable('activity_log')) {
            DB::table('activity_log')->where('subject_type', self::COMPANY_MORPH)->delete();
        }
    }

    /**
     * Recreates the shape only. The rows, the permissions, the Accounts role
     * and the media and activity rows are not restored — rolling back gives you
     * empty tables, not the billing setup.
     */
    public function down(): void
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

        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
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
};
