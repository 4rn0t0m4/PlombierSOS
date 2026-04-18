<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimRequest extends Model
{
    protected $fillable = [
        'plumber_id',
        'name',
        'email',
        'phone',
        'role',
        'message',
        'status',
        'admin_notes',
    ];

    public function plumber(): BelongsTo
    {
        return $this->belongsTo(Plumber::class);
    }
}
