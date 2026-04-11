<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\Plombier;
use App\Models\Ville;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DemandeController extends Controller
{
    public function create(Request $request)
    {
        $plombier = $request->has('plombier_id')
            ? Plombier::find($request->input('plombier_id'))
            : null;

        return view('demande.create', compact('plombier'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plombier_id' => 'nullable|exists:plombiers,id',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:20',
            'cp' => 'required|string|max:5',
            'ville' => 'nullable|string|max:255',
            'description' => 'required|string|max:5000',
            'urgence' => 'required|in:normale,urgente,tres_urgente',
            'type' => 'required|in:depannage,installation,entretien,devis',
        ]);

        $demande = Demande::create($validated);

        // Notifier le plombier si spécifié
        if ($demande->plombier && $demande->plombier->email) {
            Mail::raw(
                "Nouvelle demande d'intervention :\n\n"
                . "Type : {$demande->type}\n"
                . "Urgence : {$demande->urgence}\n"
                . "Nom : {$demande->nom}\n"
                . "Tél : {$demande->telephone}\n"
                . "Lieu : {$demande->cp} {$demande->ville}\n\n"
                . "Description :\n{$demande->description}",
                function ($message) use ($demande) {
                    $message->to($demande->plombier->email)
                        ->subject('Plombier SOS - Nouvelle demande ' . $demande->urgence);
                }
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('home')->with('success', 'Votre demande a été envoyée. Vous serez contacté rapidement.');
    }
}
