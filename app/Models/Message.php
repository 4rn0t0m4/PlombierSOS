<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['plumber_id', 'subject', 'email', 'name', 'phone', 'content'];

    public function plumber(): BelongsTo
    {
        return $this->belongsTo(Plumber::class);
    }
}
