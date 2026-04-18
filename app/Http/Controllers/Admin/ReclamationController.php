<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClaimRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
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

        if ($validated['status'] === 'approved') {
            $user = User::where('email', $claim->email)->first();

            if (! $user) {
                $user = User::create([
                    'email' => $claim->email,
                    'username' => Str::slug($claim->name, '_'),
                    'first_name' => $claim->name,
                    'phone' => $claim->phone,
                    'password' => Str::random(32),
                ]);
            }

            if (! $user->plumbers()->where('plumber_id', $claim->plumber_id)->exists()) {
                $user->plumbers()->attach($claim->plumber_id);
            }

            $token = Password::createToken($user);
            $resetUrl = url("/reinitialiser-mot-de-passe/{$token}?email=".urlencode($user->email));

            Mail::send('emails.claim-approved', [
                'name' => $claim->name,
                'plumberName' => $claim->plumber->title,
                'email' => $claim->email,
                'resetUrl' => $resetUrl,
                'adminNotes' => $validated['admin_notes'],
            ], function ($msg) use ($claim) {
                $msg->to($claim->email)
                    ->subject('Plombier SOS - Votre espace professionnel est prêt');
            });
        } else {
            Mail::send('emails.claim-rejected', [
                'name' => $claim->name,
                'plumberName' => $claim->plumber->title,
                'adminNotes' => $validated['admin_notes'],
            ], function ($msg) use ($claim) {
                $msg->to($claim->email)
                    ->subject('Plombier SOS - Réclamation refusée');
            });
        }

        return redirect()->route('admin.reclamations.index')
            ->with('success', "Réclamation {$statusLabel}."
                .($validated['status'] === 'approved' ? ' Compte pro créé/lié, email envoyé.' : ''));
    }
}
