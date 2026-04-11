<x-layouts.app :title="'Plombier à ' . $ville->nom_ville . ' (' . $ville->code_postal . ') - Plombier SOS'">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Accueil</a>
            <span class="mx-1">/</span>
            <a href="{{ route('departement.show', $ville->departementRelation->departement_url ?? '') }}" class="hover:text-blue-600">{{ $ville->departementRelation->departement ?? '' }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $ville->nom_ville }}</span>
        </nav>
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Plombier à {{ $ville->nom_ville }}</h1>
        @if($plombiers->isNotEmpty())
            <div class="space-y-4">
                @foreach($plombiers as $plombier)
                    <x-plombier-card :plombier="$plombier" :rank="$plombier->classement_ville ?: null" />
                @endforeach
            </div>
            <div class="mt-6">{{ $plombiers->links() }}</div>
        @else
            <p class="text-gray-500">Aucun plombier trouvé à {{ $ville->nom_ville }}.</p>
        @endif
    </div>
</x-layouts.app>
