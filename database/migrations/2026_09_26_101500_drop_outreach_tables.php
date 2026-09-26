<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the cold-outreach feature's storage.
 *
 * The create migration is deleted rather than kept alongside this one, but a
 * deployed environment has already run it, so the tables have to be dropped
 * forward rather than by rewinding history. dropIfExists covers the environment
 * that never ran the original.
 *
 * Sends are dropped before contacts: the foreign key points that way, and MySQL
 * refuses to drop a parent while a child references it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('outreach_sends');
        Schema::dropIfExists('outreach_suppressions');
        Schema::dropIfExists('outreach_contacts');

        // Shield generated one permission per ability against a resource that no
        // longer exists. shield:generate only adds, so without this they linger
        // as unassignable checkboxes in the role editor for ever.
        if (Schema::hasTable('permissions')) {
            $stale = DB::table('permissions')->where('name', 'like', '%outreach::contact')->pluck('id');

            if ($stale->isNotEmpty()) {
                foreach (['role_has_permissions', 'model_has_permissions'] as $pivot) {
                    if (Schema::hasTable($pivot)) {
                        DB::table($pivot)->whereIn('permission_id', $stale)->delete();
                    }
                }

                DB::table('permissions')->whereIn('id', $stale)->delete();
            }
        }
    }

    /**
     * Recreates the shape only. The contact list itself lived in a spreadsheet
     * outside the repo, so nothing here can restore the data — rolling back
     * gives you empty tables, not the campaign.
     */
    public function down(): void
    {
        Schema::create('outreach_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('organizer')->nullable();
            $table->string('organizer_short')->nullable();
            $table->string('event')->nullable();
            $table->string('event_city')->nullable();
            $table->string('event_dates')->nullable();
            $table->date('event_starts_on')->nullable();
            $table->unsignedSmallInteger('event_count')->default(1);
            $table->string('locations')->nullable();
            $table->string('status')->default('Not Contacted')->index();
            $table->string('priority')->nullable();
            $table->string('owner')->nullable();
            $table->date('email_sent_on')->nullable();
            $table->date('follow_up_1_on')->nullable();
            $table->date('follow_up_2_on')->nullable();
            $table->date('last_contact')->nullable();
            $table->date('next_action_on')->nullable()->index();
            $table->boolean('replied')->default(false);
            $table->string('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_batch')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('outreach_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outreach_contact_id')->constrained()->cascadeOnDelete();
            $table->string('step');
            $table->string('status');
            $table->string('subject')->nullable();
            $table->string('message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['outreach_contact_id', 'step', 'status']);
        });

        Schema::create('outreach_suppressions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }
};
