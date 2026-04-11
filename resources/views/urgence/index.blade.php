<x-layouts.app title="Plombier urgence 24h/24 - Plombier SOS" description="Trouvez un plombier disponible en urgence 24h/24 près de chez vous.">
    <section class="bg-red-600 text-white py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Urgence plomberie</h1>
            <p class="text-red-100 text-lg mb-8">Fuite d'eau, canalisation bouchée, panne de chaudière ? Trouvez un plombier disponible maintenant.</p>

            <form action="{{ route('urgence') }}" method="GET" class="flex flex-col sm:flex-row gap-3 max-w-xl mx-auto">
                <div class="flex-1 relative" x-data="villeAutocomplete()" @click.outside="open = false" x-init="query = '{{ $villeNom ?? '' }}'">
                    <input type="text" name="ville" placeholder="Votre ville..." x-model="query" @input="search()" @focus="open = results.length > 0" autocomplete="off" class="w-full px-4 py-3 rounded-lg bg-white text-gray-900 placeholder-gray-400">
                    <ul x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="item in results" :key="item.label">
                            <li @click="select(item)" class="px-4 py-2 cursor-pointer hover:bg-red-50 text-gray-900 text-sm" x-text="item.label"></li>
                        </template>
                    </ul>
                </div>
                <button type="submit" class="bg-white text-red-600 font-bold px-8 py-3 rounded-lg hover:bg-red-50 transition">Trouver un plombier</button>
            </form>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-8">
        @if($plombiers->isNotEmpty())
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                <span class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
                {{ $plombiers->count() }} plombier(s) disponible(s) en urgence
                @if($villeNom) près de {{ $villeNom }} @endif
            </h2>
            <div class="space-y-4">
                @foreach($plombiers as $plombier)
                    <div class="bg-white rounded-lg shadow-sm border p-5 border-l-4 border-l-red-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <a href="{{ $plombier->url }}" class="text-lg font-bold text-gray-900 hover:text-blue-600">{{ $plombier->titre }}</a>
                                <p class="text-sm text-gray-500">{{ $plombier->adresse }} {{ $plombier->cp }} {{ $plombier->ville }}</p>
                                @if(isset($plombier->distance))
                                    <p class="text-sm text-blue-600 mt-1">à {{ number_format($plombier->distance, 1, ',', '') }} km</p>
                                @endif
                            </div>
                            <div class="text-right">
                                @if($plombier->google_rating)
                                    <span class="text-yellow-500 font-bold">{{ $plombier->google_rating }}/5</span>
                                    <p class="text-xs text-gray-400">{{ $plombier->google_nb_avis }} avis</p>
                                @endif
                            </div>
                        </div>
                        @if($plombier->telephone)
                            <div class="mt-3">
                                <x-phone-reveal :phone="$plombier->telephone" :plombier-id="$plombier->id" />
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif($villeNom)
            <p class="text-gray-500 text-center py-12">Aucun plombier d'urgence trouvé près de {{ $villeNom }}. Essayez une ville voisine.</p>
        @else
            <p class="text-gray-500 text-center py-12">Entrez votre ville pour trouver un plombier d'urgence.</p>
        @endif
    </div>
</x-layouts.app>
