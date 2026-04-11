<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plumber;
use App\Services\SlugService;
use Illuminate\Http\Request;

class PlombierController extends Controller
{
    public function index(Request $request)
    {
        $query = Plumber::query()->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('city', 'like', "%{$search}%")->orWhere('postal_code', 'like', "%{$search}%"));
        }

        if ($request->filled('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $plombiers = $query->paginate(25)->withQueryString();

        return view('admin.plombiers.index', compact('plombiers'));
    }

    public function edit(Plumber $plombier)
    {
        return view('admin.plombiers.edit', compact('plombier'));
    }

    public function update(Request $request, Plumber $plombier)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|integer|in:0,1,2,3',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:5',
            'city' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:3',
            'description' => 'nullable|string',
            'pricing' => 'nullable|string',
            'siret' => 'nullable|string|max:14',
            'service_radius' => 'integer|min:1|max:200',
            'emergency_24h' => 'boolean',
            'free_quote' => 'boolean',
            'rge_certified' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validated['title'] !== $plombier->title) {
            $validated['slug'] = SlugService::generate($validated['title']);
        }

        $validated['emergency_24h'] = $request->boolean('emergency_24h');
        $validated['free_quote'] = $request->boolean('free_quote');
        $validated['rge_certified'] = $request->boolean('rge_certified');
        $validated['is_active'] = $request->boolean('is_active');

        $plombier->update($validated);

        return redirect()->route('admin.plombiers.index')->with('success', "Plombier « {$plombier->title} » mis à jour.");
    }

    public function toggleValide(Plumber $plombier)
    {
        $plombier->update(['is_active' => ! $plombier->is_active]);
        $statut = $plombier->is_active ? 'validé' : 'désactivé';

        return back()->with('success', "Plombier « {$plombier->title} » {$statut}.");
    }

    public function destroy(Plumber $plombier)
    {
        $title = $plombier->title;
        $plombier->delete();

        return redirect()->route('admin.plombiers.index')->with('success', "Plombier « {$title} » supprimé.");
    }
}
