<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Models\Demande;
use App\Models\Plombier;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'plombiers' => Plombier::count(),
            'plombiers_valides' => Plombier::where('valide', true)->count(),
            'avis_en_attente' => Avis::where('valide', false)->where('refus', false)->count(),
            'demandes_nouvelles' => Demande::where('statut', 'nouvelle')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
