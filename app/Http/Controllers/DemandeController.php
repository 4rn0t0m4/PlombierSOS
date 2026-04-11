<?php

namespace App\Http\Controllers;

use App\Models\Plumber;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DemandeController extends Controller
{
    public function create(Request $request)
    {
        $plombier = $request->has('plumber_id')
            ? Plumber::find($request->input('plumber_id'))
            : null;

        return view('demande.create', compact('plombier'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plumber_id' => 'nullable|exists:plumbers,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'postal_code' => 'required|string|max:5',
            'city' => 'nullable|string|max:255',
            'description' => 'required|string|max:5000',
            'urgency' => 'required|in:normal,urgent,very_urgent',
            'type' => 'required|in:repair,installation,maintenance,quote',
        ]);

        $serviceRequest = ServiceRequest::create($validated);

        // Notifier le plombier si spécifié
        if ($serviceRequest->plumber && $serviceRequest->plumber->email) {
            Mail::raw(
                "Nouvelle demande d'intervention :\n\n"
                ."Type : {$serviceRequest->type}\n"
                ."Urgence : {$serviceRequest->urgency}\n"
                ."Nom : {$serviceRequest->name}\n"
                ."Tél : {$serviceRequest->phone}\n"
                ."Lieu : {$serviceRequest->postal_code} {$serviceRequest->city}\n\n"
                ."Description :\n{$serviceRequest->description}",
                function ($message) use ($serviceRequest) {
                    $message->to($serviceRequest->plumber->email)
                        ->subject('Plombier SOS - Nouvelle demande '.$serviceRequest->urgency);
                }
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('home')->with('success', 'Votre demande a été envoyée. Vous serez contacté rapidement.');
    }
}
