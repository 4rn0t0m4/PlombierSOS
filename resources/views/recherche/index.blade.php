<x-layouts.app title="Rechercher un plombier - Plombier SOS" description="Recherchez un plombier, chauffagiste ou dépanneur par ville.">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Rechercher un plombier</h1>

        <form action="{{ route('recherche') }}" method="GET" class="bg-white border rounded-lg p-6 mb-8">
            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nom</label>
                    <input type="text" name="nom" value="{{ $nom ?? '' }}" class="w-full border rounded-lg px-3 py-2" placeholder="Plombier...">
                </div>
                <div x-data="villeAutocomplete()" @click.outside="open = false" x-init="query = '{{ $villeNom ?? '' }}'">
                    <label class="block text-sm font-medium mb-1">Ville</label>
                    <div class="relative">
                        <input type="text" name="ville" x-model="query" @input="search()" @focus="open = results.length > 0" autocomplete="off" class="w-full border rounded-lg px-3 py-2" placeholder="Ville...">
                        <ul x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="item in results" :key="item.label">
                                <li @click="select(item)" class="px-4 py-2 cursor-pointer hover:bg-blue-50 text-gray-900 text-sm" x-text="item.label"></li>
                            </template>
                        </ul>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Type</label>
                    <select name="type" class="w-full border rounded-lg px-3 py-2">
                        <option value="">Tous</option>
                        <option value="0" @selected(($type ?? '') === '0')>Plombier</option>
                        <option value="1" @selected(($type ?? '') === '1')>Chauffagiste</option>
                        <option value="2" @selected(($type ?? '') === '2')>Plombier-Chauffagiste</option>
                        <option value="3" @selected(($type ?? '') === '3')>Dépanneur urgence</option>
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="urgence" value="1" @checked($urgence ?? false) class="rounded">
                        Urgence 24h
                    </label>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-900 text-white px-6 py-2 rounded-lg hover:bg-blue-800">Rechercher</button>
                </div>
            </div>
        </form>

        @if($plombiers->isNotEmpty())
            <p class="text-sm text-gray-500 mb-4">{{ $plombiers->total() }} résultat(s)</p>
            <div class="space-y-4">
                @foreach($plombiers as $plombier)
                    <x-plombier-card :plombier="$plombier" />
                @endforeach
            </div>
            <div class="mt-6">{{ $plombiers->withQueryString()->links() }}</div>
        @else
            <p class="text-gray-500">Aucun résultat.</p>
        @endif
    </div>
</x-layouts.app>
