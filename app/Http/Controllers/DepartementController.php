<?php

namespace App\Http\Controllers;

use App\Models\Department;

class DepartementController extends Controller
{
    public function show(string $slug)
    {
        $department = Department::where('slug', $slug)->firstOrFail();
        $cities = $department->cities()
            ->withCount(['plumbers' => fn ($q) => $q->where('is_active', true)])
            ->having('plumbers_count', '>', 0)
            ->orderBy('name')
            ->get();

        return view('departement.show', compact('department', 'cities'));
    }
}
