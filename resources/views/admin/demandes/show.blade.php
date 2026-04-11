@extends('admin.layouts.app')

@section('content')
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.demandes.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <h1 class="text-2xl font-bold">Demande #{{ $demande->id }}</h1>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            {{-- Client --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Informations client</h2>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Nom</dt><dd class="font-medium">{{ $demande->name }}</dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd>{{ $demande->email }}</dd></div>
                    <div><dt class="text-gray-500">Téléphone</dt><dd>{{ $demande->phone ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Localisation</dt><dd>{{ $demande->city }} ({{ $demande->postal_code }})</dd></div>
                </dl>
            </div>

            {{-- Description --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Description de l'intervention</h2>
                <div class="flex gap-3 mb-3">
                    @php
                        $colors = ['normal' => 'bg-gray-100 text-gray-700', 'urgent' => 'bg-orange-100 text-orange-700', 'very_urgent' => 'bg-red-100 text-red-700'];
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded {{ $colors[$demande->urgency] ?? '' }}">Urgence : {{ $demande->urgency }}</span>
                    @if($demande->type)
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">{{ $demande->type }}</span>
                    @endif
                </div>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $demande->description }}</p>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Statut --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Statut</h2>
                <form action="{{ route('admin.demandes.update-statut', $demande) }}" method="POST" class="space-y-3">
                    @csrf
                    <select name="status" class="w-full border rounded px-3 py-2 text-sm">
                        @foreach(['new', 'sent', 'accepted', 'refused', 'completed'] as $status)
                            <option value="{{ $status }}" {{ $demande->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button class="w-full bg-blue-600 text-white text-sm py-2 rounded hover:bg-blue-700">Mettre à jour</button>
                </form>
            </div>

            {{-- Plombier --}}
            @if($demande->plumber)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Plombier associé</h2>
                    <a href="{{ route('admin.plombiers.edit', $demande->plumber) }}" class="text-blue-600 hover:underline text-sm">{{ $demande->plumber->title }}</a>
                    <p class="text-xs text-gray-500 mt-1">{{ $demande->plumber->city }} ({{ $demande->plumber->postal_code }})</p>
                </div>
            @endif

            {{-- Dates --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Dates</h2>
                <dl class="text-sm space-y-2">
                    <div><dt class="text-gray-500">Créée le</dt><dd>{{ $demande->created_at->format('d/m/Y à H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Mise à jour</dt><dd>{{ $demande->updated_at->format('d/m/Y à H:i') }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
@endsection
