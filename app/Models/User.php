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
        'email', 'password', 'username', 'last_name', 'first_name',
        'phone', 'postal_code', 'city', 'city_id', 'is_admin',
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

    public function plumbers(): BelongsToMany
    {
        return $this->belongsToMany(Plumber::class, 'plumber_user')->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
