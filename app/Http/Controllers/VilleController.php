<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Department;
use App\Models\Plumber;

class VilleController extends Controller
{
    public function show(string $deptSlug, string $villeSlug)
    {
        $department = Department::where('slug', $deptSlug)->firstOrFail();
        $city = City::where('slug', $villeSlug)->where('department', $department->number)->firstOrFail();

        $query = Plumber::active()
            ->where(function ($q) use ($city) {
                $q->where('city_id', $city->id)
                    ->orWhere(function ($q2) use ($city) {
                        $q2->where('city', 'LIKE', $city->name.'%')
                            ->where('department', $city->department);
                    });
            })
            ->orderByRaw('city_ranking = 0 ASC, city_ranking ASC, average_rating DESC');

        if ($query->count() === 0 && $city->latitude && $city->longitude) {
            $query = Plumber::active()->nearby($city->latitude, $city->longitude, 20);
        }

        $plumbers = $query->with('schedules')->paginate(20);

        return view('ville.show', compact('department', 'city', 'plumbers'));
    }
}
