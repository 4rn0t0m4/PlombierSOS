<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Plumber;

class VilleController extends Controller
{
    public function show(string $slug)
    {
        $city = City::where('slug', $slug)->firstOrFail();

        $query = Plumber::active()->where('city_id', $city->id)
            ->orderByRaw('city_ranking = 0 ASC, city_ranking ASC, average_rating DESC');

        if ($query->count() === 0 && $city->latitude && $city->longitude) {
            $query = Plumber::active()->nearby($city->latitude, $city->longitude, 20);
        }

        $plombiers = $query->with('schedules')->paginate(20);

        return view('ville.show', compact('city', 'plombiers'));
    }
}
