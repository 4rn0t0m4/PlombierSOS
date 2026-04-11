<x-layouts.app title="Plombier SOS - Trouvez un plombier en urgence près de chez vous" description="Annuaire des plombiers, chauffagistes et dépanneurs en France. Urgence 24h/24, devis gratuit, avis clients vérifiés.">
    @push('jsonld')
    @php
        $schema = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Plombier SOS',
            'url' => url('/'),
            'description' => 'Annuaire des plombiers et dépanneurs en France',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/recherche.html') . '?nom={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $schema !!}</script>
    @endpush

    {{-- Hero --}}
    <section class="bg-gradient-to-r from-blue-900 to-blue-800 text-white py-20">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Un problème de plomberie ?</h1>
            <p class="text-blue-200 text-lg mb-8">Trouvez un plombier de confiance près de chez vous, disponible 24h/24</p>

            <form action="{{ route('recherche') }}" method="GET" class="flex flex-col sm:flex-row gap-3 max-w-2xl mx-auto">
                <div class="flex-1 relative" x-data="villeAutocomplete()" @click.outside="open = false">
                    <input type="text" name="ville" placeholder="Votre ville..." x-model="query" @input="search()" @focus="open = results.length > 0" autocomplete="off" class="w-full px-4 py-3 rounded-lg bg-white text-gray-900 placeholder-gray-400">
                    <ul x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="item in results" :key="item.label">
                            <li @click="select(item)" class="px-4 py-2 cursor-pointer hover:bg-blue-50 text-gray-900 text-sm" x-text="item.label"></li>
                        </template>
                    </ul>
                </div>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-3 rounded-lg transition">Trouver un plombier</button>
            </form>

            <div class="mt-6">
                <a href="{{ route('urgence') }}" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3 rounded-lg transition text-lg">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    Urgence plomberie 24h/24
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-12">
        {{-- Urgence 24h --}}
        @if($emergencyPlumbers->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
                    Plombiers disponibles en urgence
                </h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($emergencyPlumbers as $plombier)
                        <div class="bg-white rounded-lg shadow-sm border p-4 hover:shadow-md transition border-l-4 border-l-red-500">
                            <a href="{{ $plombier->url }}" class="font-semibold text-gray-900 hover:text-blue-600">{{ $plombier->title }}</a>
                            <p class="text-sm text-gray-500 mt-1">{{ $plombier->city }} ({{ $plombier->postal_code }})</p>
                            @if($plombier->google_rating)
                                <p class="text-sm mt-1"><span class="text-yellow-500 font-semibold">{{ $plombier->google_rating }}/5</span> <span class="text-gray-400">({{ $plombier->google_reviews_count }} avis Google)</span></p>
                            @endif
                            <div class="flex gap-2 mt-2">
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Urgence 24h</span>
                                @if($plombier->free_quote)
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Devis gratuit</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Derniers avis --}}
        @if($latestReviews->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Derniers avis clients</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestReviews as $review)
                        <div class="bg-white rounded-lg shadow-sm border p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-yellow-500 font-bold">{{ number_format($review->average_rating, 1, ',', '') }}/5</span>
                                <span class="text-xs text-gray-400">{{ $review->intervention_type ?? 'Intervention' }}</span>
                            </div>
                            <h3 class="font-semibold text-gray-900">{{ $review->title }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($review->content, 100) }}</p>
                            <div class="mt-3 flex justify-between items-center text-xs text-gray-400">
                                <span>par {{ $review->author_name }}</span>
                                <a href="{{ $review->plumber->url }}" class="text-blue-600 hover:underline">{{ $review->plumber->title }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Départements --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Plombier par département</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                @foreach($departments as $dept)
                    <a href="{{ route('departement.show', $dept->slug) }}"
                       class="text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg transition">
                        {{ $dept->number }} - {{ $dept->name }}
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.app>
