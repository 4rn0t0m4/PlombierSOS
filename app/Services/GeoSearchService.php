<?php

namespace App\Services;

use App\Models\Plumber;
use Illuminate\Database\Eloquent\Builder;

class GeoSearchService
{
    public function nearby(float $lat, float $lng, float $radiusKm = 10, int $limit = 10): Builder
    {
        return Plumber::active()
            ->selectRaw(
                '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$lat, $lng, $lat]
            )
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance')
            ->limit($limit);
    }
}
