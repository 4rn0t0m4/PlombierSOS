<?php

namespace App\Http\Controllers;

use App\Models\Plombier;
use App\Services\GeoSearchService;

class PlombierController extends Controller
{
    public function show(string $slug, int $type, GeoSearchService $geoService)
    {
        $plombier = Plombier::where('slug', $slug)->valide()->first();

        if (! $plombier) {
            abort(404);
        }

        if ($plombier->type !== $type) {
            return redirect($plombier->url, 301);
        }

        $plombier->load(['approvedAvis.user', 'horairesRelation', 'administrateurs']);

        $totalInVille = $plombier->ville_id
            ? Plombier::valide()->where('ville_id', $plombier->ville_id)->count()
            : 0;

        $nearby = collect();
        if ($plombier->latitude && $plombier->longitude) {
            $nearby = $geoService->nearby($plombier->latitude, $plombier->longitude, 15, 5)
                ->where('id', '!=', $plombier->id)
                ->get();
        }

        return view('plombier.show', compact('plombier', 'nearby', 'totalInVille'));
    }
}
