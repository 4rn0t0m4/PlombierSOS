<?php

namespace App\Http\Controllers;

use App\Models\OpeningHour;
use App\Models\Plumber;
use App\Models\PlumberPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProController extends Controller
{
    public function dashboard()
    {
        $plumbers = auth()->user()->plumbers()->withCount('reviews', 'requests')->get();

        return view('pro.dashboard', compact('plumbers'));
    }

    public function edit(Plumber $plumber)
    {
        $this->authorize($plumber);

        $plumber->load('schedules', 'photos');

        return view('pro.edit', compact('plumber'));
    }

    public function update(Request $request, Plumber $plumber)
    {
        $this->authorize($plumber);

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:500',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'emergency_24h' => 'boolean',
            'free_quote' => 'boolean',
            'rge_certified' => 'boolean',
            'service_radius' => 'nullable|integer|min:1|max:100',
            'specialties' => 'nullable|array',
            'specialties.*' => 'string|max:100',
            'schedules' => 'nullable|array',
        ]);

        $plumber->update([
            'phone' => $validated['phone'] ?? $plumber->phone,
            'mobile_phone' => $validated['mobile_phone'] ?? $plumber->mobile_phone,
            'email' => $validated['email'] ?? $plumber->email,
            'website' => $validated['website'] ?? $plumber->website,
            'address' => $validated['address'] ?? $plumber->address,
            'description' => $validated['description'] ?? $plumber->description,
            'emergency_24h' => $request->boolean('emergency_24h'),
            'free_quote' => $request->boolean('free_quote'),
            'rge_certified' => $request->boolean('rge_certified'),
            'service_radius' => $validated['service_radius'] ?? $plumber->service_radius,
            'specialties' => $validated['specialties'] ?? $plumber->specialties,
        ]);

        // Update schedules
        if ($request->has('schedules')) {
            foreach ($request->input('schedules') as $day => $data) {
                OpeningHour::updateOrCreate(
                    ['plumber_id' => $plumber->id, 'day_of_week' => $day],
                    [
                        'is_closed' => ! empty($data['is_closed']),
                        'morning_open' => $data['morning_open'] ?? null,
                        'morning_close' => $data['morning_close'] ?? null,
                        'afternoon_open' => $data['afternoon_open'] ?? null,
                        'afternoon_close' => $data['afternoon_close'] ?? null,
                    ]
                );
            }
        }

        return redirect()->route('pro.edit', $plumber)->with('success', 'Informations mises à jour.');
    }

    public function uploadPhoto(Request $request, Plumber $plumber)
    {
        $this->authorize($plumber);

        $request->validate([
            'photos' => 'required|array|max:5',
            'photos.*' => 'image|max:5120', // 5MB max par image
        ]);

        $maxOrder = $plumber->photos()->max('sort_order') ?? 0;

        foreach ($request->file('photos') as $file) {
            $path = $file->store('plumbers/'.$plumber->id, 'public');
            $plumber->photos()->create([
                'path' => $path,
                'sort_order' => ++$maxOrder,
            ]);
        }

        return back()->with('success', count($request->file('photos')).' photo(s) ajoutée(s).');
    }

    public function deletePhoto(Plumber $plumber, PlumberPhoto $photo)
    {
        $this->authorize($plumber);

        if ($photo->plumber_id !== $plumber->id) {
            abort(403);
        }

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    public function updatePhotoCaption(Request $request, Plumber $plumber, PlumberPhoto $photo)
    {
        $this->authorize($plumber);

        if ($photo->plumber_id !== $plumber->id) {
            abort(403);
        }

        $request->validate(['caption' => 'nullable|string|max:255']);
        $photo->update(['caption' => $request->input('caption')]);

        return back()->with('success', 'Légende mise à jour.');
    }

    private function authorize(Plumber $plumber): void
    {
        if (! auth()->user()->plumbers()->where('plumber_id', $plumber->id)->exists()) {
            abort(403);
        }
    }
}
