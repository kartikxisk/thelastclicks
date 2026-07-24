<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $fillable = ['email', 'source_page', 'ip', 'ua', 'unsubscribed_at'];

    protected $casts = ['unsubscribed_at' => 'datetime'];
}
