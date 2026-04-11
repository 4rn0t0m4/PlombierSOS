<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Plumber;
use App\Models\Review;

class HomeController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->get();
        $latestReviews = Review::approved()->with(['plumber', 'user'])->latest()->limit(6)->get();
        $emergencyPlumbers = Plumber::active()->where('emergency_24h', true)->with('schedules')->inRandomOrder()->limit(6)->get();

        return view('home', compact('departments', 'latestReviews', 'emergencyPlumbers'));
    }
}
