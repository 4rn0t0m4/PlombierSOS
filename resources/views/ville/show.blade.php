<x-layouts.app :title="'Plombier à ' . $city->name . ' (' . $city->postal_code . ') - Plombier SOS'">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Accueil</a>
            <span class="mx-1">/</span>
            <a href="{{ route('departement.show', $department->slug) }}" class="hover:text-blue-600">{{ $department->name }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $city->name }}</span>
        </nav>
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Plombier à {{ $city->name }}</h1>
        @if($city->seo_content)
            <div class="prose text-gray-700 mb-8">{!! $city->seo_content !!}</div>
        @endif
        @if($plumbers->isNotEmpty())
            <div class="space-y-4">
                @foreach($plumbers as $plumber)
                    <x-plombier-card :plombier="$plumber" :rank="$plumber->city_ranking ?: null" />
                @endforeach
            </div>
            <div class="mt-6">{{ $plumbers->links() }}</div>
        @else
            <p class="text-gray-500">Aucun plombier trouvé à {{ $city->name }}.</p>
        @endif
    </div>
</x-layouts.app>
