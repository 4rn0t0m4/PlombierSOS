<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Plumber;

class DepartementController extends Controller
{
    public function show(string $deptSlug)
    {
        $department = Department::where('slug', $deptSlug)->firstOrFail();
        $cities = $department->cities()
            ->withCount(['plumbers' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get()
            ->each(function ($city) use ($department) {
                $byName = Plumber::active()
                    ->where('city', 'LIKE', $city->name.'%')
                    ->where('department', $department->number)
                    ->where(function ($q) use ($city) {
                        $q->whereNull('city_id')->orWhere('city_id', '!=', $city->id);
                    })
                    ->count();
                $city->plumbers_count += $byName;
            })
            ->filter(fn ($city) => $city->plumbers_count > 0)
            ->values();

        $plumbers = Plumber::active()
            ->where('department', $department->number)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('title', 'slug', 'city', 'postal_code', 'latitude', 'longitude', 'google_rating', 'type', 'city_id')
            ->with('cityRelation.departmentRelation')
            ->get();

        $markers = $plumbers->map(fn ($p) => [
            'title' => $p->title,
            'url' => $p->url,
            'city' => $p->city,
            'postal_code' => $p->postal_code,
            'lat' => (float) $p->latitude,
            'lng' => (float) $p->longitude,
            'rating' => $p->google_rating,
            'type' => $p->type_label,
        ])->values();

        return view('departement.show', compact('department', 'cities', 'plumbers', 'markers'));
    }
}
