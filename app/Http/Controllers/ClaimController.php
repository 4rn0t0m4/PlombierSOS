<?php

namespace App\Http\Controllers;

use App\Models\ClaimRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClaimController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plumber_id' => 'required|exists:plumbers,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:owner,manager,employee',
            'message' => 'nullable|string|max:2000',
        ]);

        $claim = ClaimRequest::create($validated);

        // Notify admin
        $adminEmail = config('mail.from.address');
        if ($adminEmail) {
            $plumber = $claim->plumber;
            Mail::raw(
                "Nouvelle demande de réclamation de fiche :\n\n"
                ."Établissement : {$plumber->title}\n"
                ."ID : {$plumber->id}\n"
                ."Ville : {$plumber->city} ({$plumber->postal_code})\n\n"
                ."Demandeur :\n"
                ."Nom : {$claim->name}\n"
                ."Email : {$claim->email}\n"
                ."Tél : {$claim->phone}\n"
                ."Rôle : {$claim->role}\n\n"
                ."Message :\n{$claim->message}\n\n"
                ."Gérer : ".url('/admin/reclamations'),
                function ($msg) use ($adminEmail) {
                    $msg->to($adminEmail)
                        ->subject('Plombier SOS - Nouvelle réclamation de fiche');
                }
            );
        }

        return response()->json(['success' => true]);
    }
}
