<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moves the outreach list off the spreadsheet and into the database.
 *
 * It started as marketing-sales/Event-Organizer-Leads.xlsx plus two CSVs, which
 * was the fast way to begin and is the wrong place to end: the files are
 * gitignored and local to one machine, so the admin panel could never read them
 * and two people could never work the list at once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_contacts', function (Blueprint $table) {
            $table->id();

            // The natural key. Uniqueness is what stops a re-uploaded sheet from
            // creating a second row for someone already mid-sequence.
            $table->string('email')->unique();

            $table->string('organizer')->nullable();
            $table->string('organizer_short')->nullable();

            // Merge fields for the mail copy. Denormalised on purpose: the events
            // API is a moving target and a sent message must stay explainable
            // from the row that produced it.
            $table->string('event')->nullable();
            $table->string('event_city')->nullable();
            $table->string('event_dates')->nullable();
            $table->date('event_starts_on')->nullable();
            $table->unsignedSmallInteger('event_count')->default(1);
            $table->string('locations')->nullable();

            // Pipeline state, owned by a human.
            $table->string('status')->default('Not Contacted')->index();
            $table->string('priority')->nullable();
            $table->string('owner')->nullable();
            $table->date('email_sent_on')->nullable();
            $table->date('follow_up_1_on')->nullable();
            $table->date('follow_up_2_on')->nullable();
            $table->date('last_contact')->nullable();

            // The daily work list reads this column, so it earns an index.
            $table->date('next_action_on')->nullable()->index();

            $table->boolean('replied')->default(false);
            $table->string('outcome')->nullable();
            $table->text('notes')->nullable();

            // Which upload a row arrived on, so a bad import can be found again.
            $table->string('source_batch')->nullable()->index();

            $table->timestamps();
        });

        Schema::create('outreach_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outreach_contact_id')->constrained()->cascadeOnDelete();
            $table->string('step');
            $table->string('status');               // sent | failed
            $table->string('subject')->nullable();
            $table->string('message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // The idempotency check runs on every queued job immediately before
            // it sends, so this lookup happens once per message.
            $table->index(['outreach_contact_id', 'step', 'status']);
        });

        Schema::create('outreach_suppressions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_sends');
        Schema::dropIfExists('outreach_suppressions');
        Schema::dropIfExists('outreach_contacts');
    }
};
