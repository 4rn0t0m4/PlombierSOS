<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Demande extends Model
{
    protected $fillable = [
        'plombier_id', 'nom', 'email', 'telephone', 'cp', 'ville',
        'description', 'urgence', 'type', 'statut',
    ];

    public function plombier(): BelongsTo
    {
        return $this->belongsTo(Plombier::class);
    }
}
