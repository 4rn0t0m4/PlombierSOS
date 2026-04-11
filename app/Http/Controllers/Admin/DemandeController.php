<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class DemandeController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceRequest::with('plumber')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $demandes = $query->paginate(25)->withQueryString();

        return view('admin.demandes.index', compact('demandes'));
    }

    public function show(ServiceRequest $demande)
    {
        $demande->load('plumber');

        return view('admin.demandes.show', compact('demande'));
    }

    public function updateStatut(Request $request, ServiceRequest $demande)
    {
        $request->validate([
            'status' => 'required|in:new,sent,accepted,refused,completed',
        ]);

        $demande->update(['status' => $request->status]);

        return back()->with('success', "Statut mis à jour : {$request->status}.");
    }

    public function destroy(ServiceRequest $demande)
    {
        $demande->delete();

        return redirect()->route('admin.demandes.index')->with('success', 'Demande supprimée.');
    }
}
