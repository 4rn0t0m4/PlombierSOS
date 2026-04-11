<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Services\RatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AvisController extends Controller
{
    public function store(Request $request, RatingService $ratingService)
    {
        $rules = [
            'plombier_id' => 'required|exists:plombiers,id',
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string|max:5000',
            'note_ponctualite' => 'required|integer|min:1|max:5',
            'note_qualite' => 'required|integer|min:1|max:5',
            'note_prix' => 'required|integer|min:1|max:5',
            'note_proprete' => 'required|integer|min:1|max:5',
            'note_conseil' => 'required|integer|min:1|max:5',
            'type_intervention' => 'nullable|string|max:100',
        ];

        if (! $request->user()) {
            $rules['pseudo_auteur'] = 'required|string|max:255';
            $rules['email_auteur'] = 'required|email|max:255';
        }

        $validated = $request->validate($rules);
        $validated['ip'] = $request->ip();

        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
            $validated['email_verified_at'] = now();
        } else {
            $validated['token_validation'] = Str::random(64);
        }

        $avis = Avis::create($validated);

        if (! $request->user()) {
            $url = route('avis.confirmer', $avis->token_validation);
            $plombier = $avis->plombier->titre;

            Mail::send('emails.avis-confirmation', [
                'pseudo' => $avis->pseudo_auteur,
                'plombier' => $plombier,
                'url' => $url,
            ], function ($message) use ($avis) {
                $message->to($avis->email_auteur)
                    ->subject('Confirmez votre avis sur Plombier SOS');
            });

            return back()->with('success', 'Un email de confirmation vous a été envoyé à ' . $avis->email_auteur);
        }

        return back()->with('success', 'Votre avis a été soumis et sera publié après modération.');
    }

    public function confirmerEmail(string $token)
    {
        $avis = Avis::where('token_validation', $token)->whereNull('email_verified_at')->firstOrFail();

        $avis->update([
            'email_verified_at' => now(),
            'token_validation' => null,
        ]);

        return redirect($avis->plombier->url)->with('success', 'Votre avis sera publié après modération.');
    }
}
