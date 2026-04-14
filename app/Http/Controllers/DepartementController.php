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
            ->having('plumbers_count', '>', 0)
            ->orderBy('name')
            ->get();

        $plumbers = Plumber::active()
            ->where('department', $department->number)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('title', 'slug', 'city', 'postal_code', 'latitude', 'longitude', 'google_rating', 'type', 'city_id')
            ->with('cityRelation.departmentRelation')
            ->get();

        return view('departement.show', compact('department', 'cities', 'plumbers'));
    }
}
