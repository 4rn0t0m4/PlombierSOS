<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClaimRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        // Notify the claimant
        $statusLabel = $validated['status'] === 'approved' ? 'approuvée' : 'refusée';
        Mail::raw(
            "Bonjour {$claim->name},\n\n"
            ."Votre demande de réclamation pour la fiche \"{$claim->plumber->title}\" a été {$statusLabel}.\n\n"
            .($validated['admin_notes'] ? "Commentaire : {$validated['admin_notes']}\n\n" : '')
            .($validated['status'] === 'approved'
                ? "Vous pouvez nous contacter à ".config('mail.from.address')." pour mettre à jour vos informations.\n\n"
                : '')
            ."Cordialement,\nL'équipe Plombier SOS",
            function ($msg) use ($claim, $statusLabel) {
                $msg->to($claim->email)
                    ->subject("Plombier SOS - Réclamation {$statusLabel}");
            }
        );

        return redirect()->route('admin.reclamations.index')
            ->with('success', "Réclamation {$statusLabel}.");
    }
}
