<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Plumber;
use Illuminate\Http\Request;

class UrgenceController extends Controller
{
    public function index(Request $request)
    {
        $cityName = $request->input('ville');
        $plombiers = collect();

        if ($cityName) {
            $city = City::where('name', 'like', $cityName)->first();
            if ($city && $city->latitude && $city->longitude) {
                $plombiers = Plumber::active()
                    ->where('emergency_24h', true)
                    ->nearby($city->latitude, $city->longitude, 50)
                    ->with('schedules')
                    ->limit(20)
                    ->get();
            }
        }

        return view('urgence.index', compact('plombiers', 'cityName'));
    }
}
