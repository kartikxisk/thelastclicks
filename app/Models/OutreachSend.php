<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only record of what actually left the building.
 *
 * This, not the contact's status, is the authority on whether a message was
 * sent: a status can be edited by hand in the admin, and a re-uploaded sheet
 * can reset one, but neither un-sends the email.
 */
class OutreachSend extends Model
{
    protected $guarded = [];

    protected $casts = ['sent_at' => 'datetime'];

    /** @return BelongsTo<OutreachContact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(OutreachContact::class, 'outreach_contact_id');
    }
}
