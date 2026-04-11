<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plumber;
use App\Models\Review;
use App\Models\ServiceRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'plombiers' => Plumber::count(),
            'plombiers_valides' => Plumber::where('is_active', true)->count(),
            'avis_en_attente' => Review::where('is_approved', false)->where('is_rejected', false)->count(),
            'demandes_nouvelles' => ServiceRequest::where('status', 'new')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
