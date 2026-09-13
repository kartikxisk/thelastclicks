<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutreachContact extends Model
{
    /**
     * The pipeline, in order. A status outside NEXT_STEP is a deliberate stop —
     * Replied, Won, Lost, Bounced and Not Relevant all mean "do not mail this
     * person again", and a breakup message having been sent is the whole point
     * of the silence that follows.
     */
    public const STATUSES = [
        'Not Contacted', 'Email Sent', 'Follow-up 1', 'Follow-up 2', 'Follow-up 3',
        'Replied', 'In Discussion', 'Quote Sent', 'Won', 'Lost', 'Bounced', 'Not Relevant',
    ];

    public const NEXT_STEP = [
        'Not Contacted' => 'first-touch',
        'Email Sent' => 'follow-up-1',
        'Follow-up 1' => 'follow-up-2',
    ];

    public const STATUS_AFTER = [
        'first-touch' => 'Email Sent',
        'follow-up-1' => 'Follow-up 1',
        'follow-up-2' => 'Follow-up 2',
    ];

    public const DATE_COLUMN_FOR = [
        'first-touch' => 'email_sent_on',
        'follow-up-1' => 'follow_up_1_on',
        'follow-up-2' => 'follow_up_2_on',
    ];

    protected $guarded = [];

    protected $casts = [
        'event_starts_on' => 'date',
        'email_sent_on' => 'date',
        'follow_up_1_on' => 'date',
        'follow_up_2_on' => 'date',
        'last_contact' => 'date',
        'next_action_on' => 'date',
        'replied' => 'boolean',
    ];

    /** @return HasMany<OutreachSend, $this> */
    public function sends(): HasMany
    {
        return $this->hasMany(OutreachSend::class);
    }

    /** The step this contact is owed, or null when the sequence has stopped. */
    public function dueStep(): ?string
    {
        $step = self::NEXT_STEP[$this->status] ?? null;

        if ($step === null || $step === 'first-touch') {
            return $step;
        }

        // A follow-up is only owed once the waiting period has actually elapsed.
        // Without this the whole cadence collapses into three messages at once.
        $previous = $step === 'follow-up-1' ? 'first-touch' : 'follow-up-1';
        $sentOn = $this->{self::DATE_COLUMN_FOR[$previous]};

        if ($sentOn === null) {
            // Status claims a message went out but no date was recorded; there is
            // no way to know whether the wait has passed, so hold rather than guess.
            return null;
        }

        $wait = (int) config('outreach.follow_up_days.'.$previous);

        return $sentOn->copy()->addDays($wait)->isFuture() ? null : $step;
    }

    /** @param  Builder<OutreachContact>  $query */
    public function scopeDue(Builder $query): void
    {
        $query->whereIn('status', array_keys(self::NEXT_STEP))
            ->whereNotIn('email', OutreachSuppression::query()->select('email'));
    }

    public function isSuppressed(): bool
    {
        return OutreachSuppression::where('email', $this->email)->exists();
    }

    /** Has this exact step already gone out? The log, not the status, decides. */
    public function hasSent(string $step): bool
    {
        return $this->sends()->where('step', $step)->where('status', 'sent')->exists();
    }
}
