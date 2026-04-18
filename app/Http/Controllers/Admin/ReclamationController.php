<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClaimRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReclamationController extends Controller
{
    public function index()
    {
        $claims = ClaimRequest::with('plumber')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.reclamations.index', compact('claims'));
    }

    public function update(Request $request, ClaimRequest $claim)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $claim->update($validated);

        $statusLabel = $validated['status'] === 'approved' ? 'approuvée' : 'refusée';
        $loginInfo = '';

        // If approved, create/link user account and attach to plumber
        if ($validated['status'] === 'approved') {
            $user = User::where('email', $claim->email)->first();
            $password = null;

            if (! $user) {
                $password = Str::random(10);
                $user = User::create([
                    'email' => $claim->email,
                    'username' => Str::slug($claim->name, '_'),
                    'first_name' => $claim->name,
                    'phone' => $claim->phone,
                    'password' => $password,
                ]);
            }

            // Attach plumber to user if not already linked
            if (! $user->plumbers()->where('plumber_id', $claim->plumber_id)->exists()) {
                $user->plumbers()->attach($claim->plumber_id);
            }

            $loginInfo = "\n\nVotre espace professionnel est maintenant accessible :\n"
                ."URL : ".url('/pro')."\n"
                ."Email : {$claim->email}\n";
            if ($password) {
                $loginInfo .= "Mot de passe : {$password}\n"
                    ."(Pensez à le changer après votre première connexion)\n";
            } else {
                $loginInfo .= "(Utilisez votre mot de passe habituel)\n";
            }
        }

        Mail::raw(
            "Bonjour {$claim->name},\n\n"
            ."Votre demande de réclamation pour la fiche \"{$claim->plumber->title}\" a été {$statusLabel}.\n\n"
            .($validated['admin_notes'] ? "Commentaire : {$validated['admin_notes']}\n\n" : '')
            .$loginInfo
            ."\nCordialement,\nL'équipe Plombier SOS",
            function ($msg) use ($claim, $statusLabel) {
                $msg->to($claim->email)
                    ->subject("Plombier SOS - Réclamation {$statusLabel}");
            }
        );

        return redirect()->route('admin.reclamations.index')
            ->with('success', "Réclamation {$statusLabel}."
                .($validated['status'] === 'approved' ? ' Compte pro créé/lié.' : ''));
    }
}
