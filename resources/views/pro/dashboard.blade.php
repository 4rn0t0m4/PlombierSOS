<x-layouts.app title="Espace Pro - Plombier SOS">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Espace Pro</h1>
            <a href="{{ route('logout') }}" class="text-sm text-gray-500 hover:text-gray-700">Déconnexion</a>
        </div>

        <p class="text-gray-600 mb-6">Bonjour {{ auth()->user()->first_name ?? auth()->user()->username }}, gérez vos fiches établissement ci-dessous.</p>

        <div class="space-y-4">
            @foreach($plumbers as $plumber)
                <div class="bg-white border rounded-lg p-6 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $plumber->title }}</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ $plumber->address }} {{ $plumber->postal_code }} {{ $plumber->city }}</p>
                            <div class="flex gap-4 mt-3 text-sm text-gray-600">
                                @if($plumber->google_rating)
                                    <span>Note Google : <strong>{{ $plumber->google_rating }}/5</strong></span>
                                @endif
                                <span>{{ $plumber->reviews_count }} avis</span>
                                <span>{{ $plumber->requests_count }} demandes</span>
                            </div>
                            <div class="flex gap-2 mt-3">
                                @if($plumber->is_active)
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">En ligne</span>
                                @else
                                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Hors ligne</span>
                                @endif
                                @if($plumber->emergency_24h)
                                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Urgence 24h</span>
                                @endif
                                @if($plumber->free_quote)
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Devis gratuit</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('pro.edit', $plumber) }}" class="bg-blue-900 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-blue-800 text-center">Modifier</a>
                            <a href="{{ $plumber->url }}" target="_blank" class="text-sm text-blue-600 hover:underline text-center">Voir la fiche</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
