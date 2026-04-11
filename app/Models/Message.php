<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['plombier_id', 'email', 'nom', 'telephone', 'contenu'];

    public function plombier(): BelongsTo
    {
        return $this->belongsTo(Plombier::class);
    }
}
