<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Services\RatingService;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    public function index()
    {
        $avis = Avis::where('valide', false)->where('refus', false)
            ->with(['plombier', 'user'])->latest()->paginate(25);

        return view('admin.avis.index', compact('avis'));
    }

    public function moderer(Request $request, Avis $avis, RatingService $ratingService)
    {
        $request->validate(['action' => 'required|in:valider,refuser']);

        if ($request->action === 'valider') {
            $avis->update(['valide' => true, 'refus' => false]);
        } else {
            $avis->update(['valide' => false, 'refus' => true]);
        }

        $ratingService->recalculate($avis->plombier);

        return redirect()->route('admin.avis.index')->with('success', 'Avis modéré.');
    }
}
