<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

    public function transfer(ServiceRequest $demande)
    {
        $demande->load('plumber');

        if (! $demande->plumber?->email) {
            return back()->with('error', 'Ce plombier n\'a pas d\'email.');
        }

        Mail::raw(
            "Bonjour,\n\n"
            ."Vous avez reçu une nouvelle demande d'intervention via Plombier SOS :\n\n"
            ."Client : {$demande->name}\n"
            ."Téléphone : {$demande->phone}\n"
            ."Email : {$demande->email}\n"
            ."Localisation : {$demande->postal_code} {$demande->city}\n"
            ."Urgence : {$demande->urgency}\n"
            ."Type : {$demande->type}\n\n"
            ."Description :\n{$demande->description}\n\n"
            ."Merci de contacter ce client dans les meilleurs délais.\n\n"
            ."Cordialement,\nPlombier SOS",
            function ($message) use ($demande) {
                $message->to($demande->plumber->email)
                    ->subject('Plombier SOS - Nouvelle demande d\'intervention');
            }
        );

        $demande->update(['status' => 'sent']);

        return back()->with('success', "Demande transférée à {$demande->plumber->email}.");
    }

    public function destroy(ServiceRequest $demande)
    {
        $demande->delete();

        return redirect()->route('admin.demandes.index')->with('success', 'Demande supprimée.');
    }
}
