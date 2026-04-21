<x-layouts.app :title="'Plombier ' . $department->article . $department->name . ' - Plombier SOS'">
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_PLACES_API_KEY') }}"></script>
    @endpush

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

        {{-- Carte --}}
        @if($plumbers->isNotEmpty())
            <div class="mb-8 rounded-lg border shadow-sm" id="dept-map" style="height: 400px; width: 100%; z-index: 0;"></div>
        @endif

        {{-- Villes --}}
        @if($cities->isNotEmpty())
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Villes avec plombiers</h2>
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

    @if($plumbers->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var plumbers = @json($markers);
                var deptCode = '{{ $department->number }}';

                var map = L.map('dept-map', { scrollWheelZoom: false }).setView([{{ $department->latitude ?? 46.6 }}, {{ $department->longitude ?? 2.3 }}], 9);
                L.gridLayer.googleMutant({ type: 'roadmap', maxZoom: 20 }).addTo(map);
                setTimeout(function () { map.invalidateSize(); }, 100);

                // Load department boundary from france-geojson
                var deptSlug = '{{ $department->number }}-{{ Str::slug($department->name) }}';
                var geoUrl = 'https://raw.githubusercontent.com/gregoiredavid/france-geojson/master/departements/' + deptSlug + '/departement-' + deptSlug + '.geojson';
                fetch(geoUrl)
                    .then(function (r) { return r.json(); })
                    .then(function (geojson) {
                        L.geoJSON(geojson, {
                            style: { color: '#1e3a8a', weight: 2, fillColor: '#3b82f6', fillOpacity: 0.05 }
                        }).addTo(map);
                        map.fitBounds(L.geoJSON(geojson).getBounds(), { padding: [20, 20] });
                    })
                    .catch(function () {});


                // Add plumber markers
                plumbers.forEach(function (p) {
                    var marker = L.marker([p.lat, p.lng]).addTo(map);
                    marker.bindPopup(
                        '<strong><a href="' + p.url + '">' + p.title + '</a></strong><br>' +
                        '<span style="color:#666">' + p.type + '</span><br>' +
                        p.city + ' (' + p.postal_code + ')' +
                        (p.rating ? '<br>⭐ ' + p.rating + '/5' : '')
                    );
                });
            });
        </script>
    @endif
</x-layouts.app>
