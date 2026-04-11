<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\RatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AvisController extends Controller
{
    public function store(Request $request, RatingService $ratingService)
    {
        $rules = [
            'plumber_id' => 'required|exists:plumbers,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'punctuality_rating' => 'required|integer|min:1|max:5',
            'quality_rating' => 'required|integer|min:1|max:5',
            'price_rating' => 'required|integer|min:1|max:5',
            'cleanliness_rating' => 'required|integer|min:1|max:5',
            'advice_rating' => 'required|integer|min:1|max:5',
            'intervention_type' => 'nullable|string|max:100',
        ];

        if (! $request->user()) {
            $rules['author_username'] = 'required|string|max:255';
            $rules['author_email'] = 'required|email|max:255';
        }

        $validated = $request->validate($rules);
        $validated['ip'] = $request->ip();

        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
            $validated['email_verified_at'] = now();
        } else {
            $validated['validation_token'] = Str::random(64);
        }

        $review = Review::create($validated);

        if (! $request->user()) {
            $url = route('avis.confirmer', $review->validation_token);
            $plombier = $review->plumber->title;

            Mail::send('emails.avis-confirmation', [
                'pseudo' => $review->author_username,
                'plombier' => $plombier,
                'url' => $url,
            ], function ($message) use ($review) {
                $message->to($review->author_email)
                    ->subject('Confirmez votre avis sur Plombier SOS');
            });

            return back()->with('success', 'Un email de confirmation vous a été envoyé à '.$review->author_email);
        }

        return back()->with('success', 'Votre avis a été soumis et sera publié après modération.');
    }

    public function confirmerEmail(string $token)
    {
        $review = Review::where('validation_token', $token)->whereNull('email_verified_at')->firstOrFail();

        $review->update([
            'email_verified_at' => now(),
            'validation_token' => null,
        ]);

        return redirect($review->plumber->url)->with('success', 'Votre avis sera publié après modération.');
    }
}
