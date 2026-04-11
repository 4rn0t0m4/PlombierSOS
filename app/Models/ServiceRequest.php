<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'plumber_id', 'name', 'email', 'phone', 'postal_code', 'city',
        'description', 'urgency', 'type', 'status',
    ];

    public function plumber(): BelongsTo
    {
        return $this->belongsTo(Plumber::class);
    }
}
