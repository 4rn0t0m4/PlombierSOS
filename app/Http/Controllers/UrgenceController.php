<?php

namespace App\Http\Controllers;

use App\Models\Plombier;
use App\Models\Ville;
use Illuminate\Http\Request;

class UrgenceController extends Controller
{
    public function index(Request $request)
    {
        $villeNom = $request->input('ville');
        $plombiers = collect();

        if ($villeNom) {
            $ville = Ville::where('nom_ville', 'like', $villeNom)->first();
            if ($ville && $ville->latitude && $ville->longitude) {
                $plombiers = Plombier::valide()
                    ->where('urgence_24h', true)
                    ->nearby($ville->latitude, $ville->longitude, 50)
                    ->with('horairesRelation')
                    ->limit(20)
                    ->get();
            }
        }

        return view('urgence.index', compact('plombiers', 'villeNom'));
    }
}
