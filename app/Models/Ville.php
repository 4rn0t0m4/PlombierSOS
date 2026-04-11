<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ville extends Model
{
    protected $fillable = [
        'nom_ville', 'code_postal', 'url', 'departement', 'habitants', 'latitude', 'longitude',
    ];

    public function departementRelation(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement', 'numero');
    }

    public function plombiers(): HasMany
    {
        return $this->hasMany(Plombier::class);
    }
}
