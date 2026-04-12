<x-layouts.app :title="'Plombier ' . $department->article . $department->name . ' - Plombier SOS'">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Accueil</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $department->name }}</span>
        </nav>
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Plombier {{ $department->article }}{{ $department->name }}</h1>
        @if($department->seo_content)
            <div class="prose text-gray-700 mb-8">{!! app(\App\Services\SeoLinkService::class)->addLinks($department->seo_content) !!}</div>
        @endif
        @if($cities->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($cities as $city)
                    <a href="{{ route('ville.show', [$department->slug, $city->slug]) }}" class="bg-white border rounded-lg px-4 py-3 hover:border-blue-300 hover:bg-blue-50 transition">
                        <span class="font-medium text-gray-900">{{ $city->name }}</span>
                        <span class="text-sm text-gray-400 ml-1">({{ $city->plumbers_count }})</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Aucun plombier trouvé dans ce département.</p>
        @endif
    </div>
</x-layouts.app>
