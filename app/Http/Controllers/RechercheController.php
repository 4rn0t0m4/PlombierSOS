<?php

namespace App\Http\Controllers;

use App\Models\Plombier;
use App\Models\Ville;
use Illuminate\Http\Request;

class RechercheController extends Controller
{
    public function index(Request $request)
    {
        $query = Plombier::valide()->with('horairesRelation');

        $nom = $request->input('nom');
        $villeNom = $request->input('ville');
        $urgence = $request->boolean('urgence');
        $type = $request->input('type');

        if ($nom) {
            $query->where('titre', 'like', '%' . $nom . '%');
        }

        if ($villeNom) {
            $ville = Ville::where('nom_ville', 'like', $villeNom)->first();
            if ($ville && $ville->latitude && $ville->longitude) {
                $query->nearby($ville->latitude, $ville->longitude, 30);
            } elseif ($ville) {
                $query->where('ville_id', $ville->id);
            }
        }

        if ($urgence) {
            $query->where('urgence_24h', true);
        }

        if ($type !== null && $type !== '') {
            $query->where('type', (int) $type);
        }

        $plombiers = $query->orderByDesc('moyenne')->paginate(20);

        return view('recherche.index', compact('plombiers', 'nom', 'villeNom', 'urgence', 'type'));
    }
}
