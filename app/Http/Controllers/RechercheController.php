<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Plumber;
use Illuminate\Http\Request;

class RechercheController extends Controller
{
    public function index(Request $request)
    {
        $query = Plumber::active()->with('schedules');

        $nom = $request->input('nom');
        $cityName = $request->input('ville');
        $urgence = $request->boolean('urgence');
        $type = $request->input('type');

        if ($nom) {
            $query->where('title', 'like', '%'.$nom.'%');
        }

        if ($cityName) {
            $city = City::where('name', 'like', $cityName)->first();
            if ($city && $city->latitude && $city->longitude) {
                $query->nearby($city->latitude, $city->longitude, 30);
            } elseif ($city) {
                $query->where('city_id', $city->id);
            }
        }

        if ($urgence) {
            $query->where('emergency_24h', true);
        }

        if ($type !== null && $type !== '') {
            $query->where('type', (int) $type);
        }

        $plombiers = $query->orderByDesc('average_rating')->paginate(20);

        return view('recherche.index', compact('plombiers', 'nom', 'cityName', 'urgence', 'type'));
    }
}
