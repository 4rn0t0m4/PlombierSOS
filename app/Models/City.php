<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name', 'postal_code', 'slug', 'department', 'population', 'latitude', 'longitude',
    ];

    public function departmentRelation(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department', 'number');
    }

    public function plumbers(): HasMany
    {
        return $this->hasMany(Plumber::class, 'city_id');
    }
}
