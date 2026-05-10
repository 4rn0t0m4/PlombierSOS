<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlumberPhoto extends Model
{
    protected $fillable = ['plumber_id', 'path', 'caption', 'sort_order'];

    public function plumber(): BelongsTo
    {
        return $this->belongsTo(Plumber::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }
}
