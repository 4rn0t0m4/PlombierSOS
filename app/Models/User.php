<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'email', 'password', 'pseudo', 'nom', 'prenom',
        'telephone', 'cp', 'ville', 'ville_id', 'is_admin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function plombiers(): BelongsToMany
    {
        return $this->belongsToMany(Plombier::class)->withTimestamps();
    }

    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class);
    }
}
