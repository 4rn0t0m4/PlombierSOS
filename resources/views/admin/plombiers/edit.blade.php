@extends('admin.layouts.app')

@section('content')
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.plombiers.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <h1 class="text-2xl font-bold">Modifier : {{ $plombier->title }}</h1>
        <a href="{{ $plombier->url }}" target="_blank" class="text-sm text-blue-600 hover:underline ml-auto">Voir la fiche</a>
    </div>

    <form action="{{ route('admin.plombiers.update', $plombier) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')

        {{-- Informations générales --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Informations générales</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Nom / Titre</label>
                    <input type="text" name="title" value="{{ old('title', $plombier->title) }}" class="w-full border rounded px-3 py-2 text-sm" required>
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Type</label>
                    <select name="type" class="w-full border rounded px-3 py-2 text-sm">
                        @foreach(\App\Models\Plumber::TYPE_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ old('type', $plombier->type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $plombier->email) }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $plombier->phone) }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Portable</label>
                    <input type="text" name="mobile_phone" value="{{ old('mobile_phone', $plombier->mobile_phone) }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Site web</label>
                    <input type="url" name="website" value="{{ old('website', $plombier->website) }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">SIRET</label>
                    <input type="text" name="siret" value="{{ old('siret', $plombier->siret) }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Rayon d'intervention (km)</label>
                    <input type="number" name="service_radius" value="{{ old('service_radius', $plombier->service_radius) }}" class="w-full border rounded px-3 py-2 text-sm" min="1" max="200">
                </div>
            </div>
        </div>

        {{-- Adresse --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Adresse</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-600 mb-1">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $plombier->address) }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Code postal</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $plombier->postal_code) }}" class="w-full border rounded px-3 py-2 text-sm" maxlength="5">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $plombier->city) }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Département</label>
                    <input type="text" name="department" value="{{ old('department', $plombier->department) }}" class="w-full border rounded px-3 py-2 text-sm" maxlength="3">
                </div>
            </div>
        </div>

        {{-- Description / Tarifs --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Description et tarifs</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Description</label>
                    <textarea name="description" rows="4" class="w-full border rounded px-3 py-2 text-sm">{{ old('description', $plombier->description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tarifs</label>
                    <textarea name="pricing" rows="3" class="w-full border rounded px-3 py-2 text-sm">{{ old('pricing', $plombier->pricing) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Options --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Options</h2>
            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plombier->is_active) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm">Validé (visible sur le site)</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="emergency_24h" value="0">
                    <input type="checkbox" name="emergency_24h" value="1" {{ old('emergency_24h', $plombier->emergency_24h) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm">Urgence 24h/24</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="free_quote" value="0">
                    <input type="checkbox" name="free_quote" value="1" {{ old('free_quote', $plombier->free_quote) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm">Devis gratuit</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="rge_certified" value="0">
                    <input type="checkbox" name="rge_certified" value="1" {{ old('rge_certified', $plombier->rge_certified) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm">Agréé RGE</span>
                </label>
            </div>
        </div>

        {{-- Infos Google --}}
        @if($plombier->place_id)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Données Google Places</h2>
                <div class="grid md:grid-cols-3 gap-4 text-sm text-gray-600">
                    <div><span class="font-medium">Place ID :</span> {{ $plombier->place_id }}</div>
                    <div><span class="font-medium">Note Google :</span> {{ $plombier->google_rating ?? '-' }}/5 ({{ $plombier->google_reviews_count }} avis)</div>
                    <div><span class="font-medium">Coordonnées :</span> {{ $plombier->latitude }}, {{ $plombier->longitude }}</div>
                </div>
            </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 text-sm font-medium">Enregistrer</button>
            <a href="{{ route('admin.plombiers.index') }}" class="px-6 py-2 rounded border text-sm text-gray-600 hover:bg-gray-50">Annuler</a>
        </div>
    </form>
@endsection
