@props(['plombier'])

@php $statut = $plombier->opening_status; $prochaine = $plombier->next_opening; @endphp

@if($statut !== 'unknown')
<span class="inline-flex items-center gap-1 text-sm font-medium
    @if($statut === 'open') text-green-600
    @elseif($statut === 'closing_soon') text-orange-500
    @elseif($statut === 'opening_soon') text-blue-500
    @else text-red-500 @endif">
    <span class="w-2 h-2 rounded-full
        @if($statut === 'open') bg-green-500
        @elseif($statut === 'closing_soon') bg-orange-400
        @elseif($statut === 'opening_soon') bg-blue-400
        @else bg-red-400 @endif"></span>
    @if($statut === 'open') Ouvert
    @elseif($statut === 'closing_soon') Ferme bientôt
    @elseif($statut === 'opening_soon') Ouvre bientôt
    @else Fermé @if($prochaine)<span class="font-normal text-gray-500 ml-1">· {{ $prochaine }}</span>@endif
    @endif
</span>
@endif
