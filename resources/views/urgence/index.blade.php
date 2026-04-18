<x-layouts.app title="Plombier urgence 24h/24 - Plombier SOS" description="Trouvez un plombier disponible en urgence 24h/24 près de chez vous.">
    <section class="bg-red-600 text-white py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Urgence plomberie</h1>
            <p class="text-red-100 text-lg mb-8">Fuite d'eau, canalisation bouchée, panne de chaudière ? Trouvez un plombier disponible maintenant.</p>

            <form action="{{ route('urgence') }}" method="GET" class="flex flex-col sm:flex-row gap-3 max-w-xl mx-auto">
                <div class="flex-1 relative" x-data="villeAutocomplete()" @click.outside="open = false" x-init="query = '{{ $cityName ?? '' }}'">
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

    {{-- Chatbot CTA --}}
    <section class="bg-blue-50 border-b">
        <div class="max-w-4xl mx-auto px-4 py-6 flex flex-col sm:flex-row items-center gap-4">
            <div class="text-4xl">&#x1F4AC;</div>
            <div class="flex-1 text-center sm:text-left">
                <h2 class="text-lg font-bold text-gray-900">Besoin d'aide pour diagnostiquer votre problème ?</h2>
                <p class="text-sm text-gray-600">Notre assistant IA peut évaluer l'urgence de votre situation et vous recommander un plombier adapté près de chez vous.</p>
            </div>
            <button @click="$store.chatbot.requestOpen = true" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition whitespace-nowrap">
                Parler à l'assistant
            </button>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-8">
        @if($plombiers->isNotEmpty())
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                <span class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
                {{ $plombiers->count() }} plombier(s) disponible(s) en urgence
                @if($cityName) près de {{ $cityName }} @endif
            </h2>
            <div class="space-y-4">
                @foreach($plombiers as $plombier)
                    <div class="bg-white rounded-lg shadow-sm border p-5 border-l-4 border-l-red-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <a href="{{ $plombier->url }}" class="text-lg font-bold text-gray-900 hover:text-blue-600">{{ $plombier->title }}</a>
                                <p class="text-sm text-gray-500">{{ $plombier->address }} {{ $plombier->postal_code }} {{ $plombier->city }}</p>
                                @if(isset($plombier->distance))
                                    <p class="text-sm text-blue-600 mt-1">à {{ number_format($plombier->distance, 1, ',', '') }} km</p>
                                @endif
                            </div>
                            <div class="text-right">
                                @if($plombier->google_rating)
                                    <span class="text-yellow-500 font-bold">{{ $plombier->google_rating }}/5</span>
                                    <p class="text-xs text-gray-400">{{ $plombier->google_reviews_count }} avis</p>
                                @endif
                            </div>
                        </div>
                        @if($plombier->phone)
                            <div class="mt-3">
                                <x-phone-reveal :phone="$plombier->phone" :plombier-id="$plombier->id" />
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif($cityName)
            <p class="text-gray-500 text-center py-12">Aucun plombier d'urgence trouvé près de {{ $cityName }}. Essayez une ville voisine.</p>
        @else
            <p class="text-gray-500 text-center py-12">Entrez votre ville pour trouver un plombier d'urgence.</p>
        @endif
    </div>
</x-layouts.app>
