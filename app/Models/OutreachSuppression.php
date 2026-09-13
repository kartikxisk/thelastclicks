<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Addresses that must never be mailed again.
 *
 * Checked immediately before each send rather than only when a batch is built,
 * so an opt-out added while a run is in flight still takes effect.
 */
class OutreachSuppression extends Model
{
    protected $guarded = [];
}
