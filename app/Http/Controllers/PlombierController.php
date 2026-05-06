<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Department;
use App\Models\Plumber;
use App\Services\GeoSearchService;

class PlombierController extends Controller
{
    public function show(string $deptSlug, string $villeSlug, string $plombierSlug, GeoSearchService $geoService)
    {
        $department = Department::where('slug', $deptSlug)->firstOrFail();
        $city = City::where('slug', $villeSlug)->where('department', $department->number)->firstOrFail();
        $plumber = Plumber::where('slug', $plombierSlug)->active()->first();

        if (! $plumber) {
            return redirect()->route('ville.show', [$deptSlug, $villeSlug], 301);
        }

        $plumber->load(['approvedReviews.user', 'schedules', 'administrators']);

        $totalInCity = Plumber::active()->where('city_id', $city->id)->count();

        $nearby = collect();
        if ($plumber->latitude && $plumber->longitude) {
            $nearby = $geoService->nearby($plumber->latitude, $plumber->longitude, 15, 5)
                ->where('id', '!=', $plumber->id)
                ->get();
        }

        return view('plombier.show', compact('department', 'city', 'plumber', 'nearby', 'totalInCity'));
    }
}
