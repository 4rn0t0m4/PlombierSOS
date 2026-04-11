<?php

namespace App\Http\Controllers;

use App\Models\Plumber;
use App\Services\GeoSearchService;

class PlombierController extends Controller
{
    public function show(string $slug, int $type, GeoSearchService $geoService)
    {
        $plombier = Plumber::where('slug', $slug)->active()->first();

        if (! $plombier) {
            abort(404);
        }

        if ($plombier->type !== $type) {
            return redirect($plombier->url, 301);
        }

        $plombier->load(['approvedReviews.user', 'schedules', 'administrateurs']);

        $totalInCity = $plombier->city_id
            ? Plumber::active()->where('city_id', $plombier->city_id)->count()
            : 0;

        $nearby = collect();
        if ($plombier->latitude && $plombier->longitude) {
            $nearby = $geoService->nearby($plombier->latitude, $plombier->longitude, 15, 5)
                ->where('id', '!=', $plombier->id)
                ->get();
        }

        return view('plombier.show', compact('plombier', 'nearby', 'totalInCity'));
    }
}
