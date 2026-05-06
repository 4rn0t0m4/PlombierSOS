<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('pages.contact');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:100',
            'message' => 'required|string|max:5000',
        ]);

        $subjectLabels = [
            'question' => 'Question générale',
            'suggestion' => 'Suggestion',
            'partenariat' => 'Partenariat',
            'reclamation' => 'Réclamation de fiche',
            'bug' => 'Signaler un problème',
            'autre' => 'Autre',
        ];

        $subjectLabel = $subjectLabels[$validated['subject']] ?? $validated['subject'];

        Mail::raw(
            "Nouveau message de contact :\n\n"
            ."Nom : {$validated['name']}\n"
            ."Email : {$validated['email']}\n"
            ."Sujet : {$subjectLabel}\n\n"
            ."Message :\n{$validated['message']}",
            function ($msg) use ($validated, $subjectLabel) {
                $msg->to('contact@plombier-sos.fr')
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject("Plombier SOS - Contact : {$subjectLabel}");
            }
        );

        return response()->json(['success' => true]);
    }
}
