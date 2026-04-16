<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Plumber;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        // Global stats
        $stats = [
            'total_plumbers' => Plumber::count(),
            'active_plumbers' => Plumber::where('is_active', true)->count(),
            'with_email' => Plumber::where('email', '!=', '')->whereNotNull('email')->count(),
            'with_website' => Plumber::whereNotNull('website')->where('website', '!=', '')->count(),
            'with_reviews' => Plumber::where('google_reviews_count', '>', 0)->count(),
            'with_seo' => Plumber::whereNotNull('seo_content')->where('seo_content', '!=', '')->count(),
            'avg_rating' => Plumber::whereNotNull('google_rating')->avg('google_rating'),
        ];

        // Plumbers per department
        $departments = Department::select('departments.number', 'departments.name', 'departments.slug')
            ->selectRaw('COUNT(plumbers.id) as plumbers_count')
            ->selectRaw('SUM(CASE WHEN plumbers.is_active = 1 THEN 1 ELSE 0 END) as active_count')
            ->selectRaw('SUM(CASE WHEN plumbers.place_id IS NOT NULL THEN 1 ELSE 0 END) as google_imported_count')
            ->leftJoin('plumbers', function ($join) {
                $join->on('departments.number', '=', 'plumbers.department');
            })
            ->groupBy('departments.number', 'departments.name', 'departments.slug')
            ->orderBy('departments.number')
            ->get();

        // Import progress
        $importProgress = DB::table('google_import_progress')
            ->orderBy('department')
            ->get()
            ->keyBy('department');

        // Recent imports (last 24h)
        $recentImports = Plumber::where('created_at', '>=', now()->subDay())
            ->count();

        return view('admin.stats', compact('stats', 'departments', 'importProgress', 'recentImports'));
    }
}
