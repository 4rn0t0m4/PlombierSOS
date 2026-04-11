@props(['plombier', 'rank' => null])

<div class="bg-white rounded-lg shadow-sm border p-4 hover:shadow-md transition">
    <div class="flex justify-between items-start">
        <div class="flex items-start gap-3">
            @if($rank)
                <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold text-sm">{{ $rank }}</span>
            @endif
            <div>
                <a href="{{ $plombier->url }}" class="text-lg font-semibold text-gray-900 hover:text-blue-600">{{ $plombier->titre }}</a>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-sm text-gray-500">{{ $plombier->type_label }}</span>
                    <x-statut-ouverture :plombier="$plombier" />
                </div>
                @if($plombier->ville)
                    <p class="text-sm text-gray-500">{{ $plombier->adresse }} {{ $plombier->cp }} {{ $plombier->ville }}</p>
                @endif
                <div class="flex gap-1.5 mt-1.5">
                    @if($plombier->urgence_24h)
                        <span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded">Urgence 24h</span>
                    @endif
                    @if($plombier->devis_gratuit)
                        <span class="text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded">Devis gratuit</span>
                    @endif
                    @if($plombier->agree_rge)
                        <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">RGE</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="text-right">
            @if($plombier->google_rating)
                <div class="flex items-center gap-1">
                    <x-star-rating :rating="$plombier->google_rating" size="w-4 h-4" />
                    <span class="text-sm text-gray-500">{{ number_format($plombier->google_rating, 1, ',', '') }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $plombier->google_nb_avis }} avis</p>
            @endif
        </div>
    </div>
</div>
