<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $primaryKey = 'number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'number', 'name', 'slug', 'region', 'article', 'latitude', 'longitude',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'department', 'number');
    }
}
