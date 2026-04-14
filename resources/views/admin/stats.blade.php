@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Statistiques</h1>

    {{-- Global stats --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">Total plombiers</p>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_plumbers']) }}</p>
            <p class="text-xs text-green-600 mt-1">{{ $stats['active_plumbers'] }} actifs</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">Avec email</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['with_email'] }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['total_plumbers'] ? round($stats['with_email'] / $stats['total_plumbers'] * 100) : 0 }}% du total</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">Note Google moyenne</p>
            <p class="text-3xl font-bold text-yellow-500">{{ number_format($stats['avg_rating'], 1, ',', '') }}/5</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['with_reviews'] }} avec avis</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">Importés (24h)</p>
            <p class="text-3xl font-bold text-green-600">{{ $recentImports }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['with_seo'] }} avec contenu SEO</p>
        </div>
    </div>

    {{-- Department table --}}
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="font-semibold text-gray-900">Plombiers par département</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Dept</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Nom</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Plombiers</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Actifs</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Import</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Progression</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($departments as $dept)
                    @php
                        $progress = $importProgress[$dept->number] ?? null;
                        $cityOffset = $progress->city_offset ?? 0;
                        $queryOffset = $progress->query_offset ?? 0;
                        $isCompleted = $progress->completed ?? false;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono text-gray-500">{{ $dept->number }}</td>
                        <td class="px-4 py-2">
                            <a href="/{{ $dept->slug }}" target="_blank" class="text-blue-600 hover:underline">{{ $dept->name }}</a>
                        </td>
                        <td class="px-4 py-2 text-center font-medium {{ $dept->plumbers_count > 0 ? 'text-gray-900' : 'text-gray-300' }}">
                            {{ $dept->plumbers_count }}
                        </td>
                        <td class="px-4 py-2 text-center {{ $dept->active_count > 0 ? 'text-green-600' : 'text-gray-300' }}">
                            {{ $dept->active_count }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($progress)
                                <span class="text-xs {{ $isCompleted ? 'text-green-600' : 'text-orange-500' }}">
                                    {{ $progress->total_imported ?? 0 }} importés
                                </span>
                            @else
                                <span class="text-xs text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right">
                            @if($progress)
                                @if($isCompleted)
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Terminé (offset {{ $cityOffset }})</span>
                                @else
                                    <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded">En cours (villes {{ $cityOffset + 1 }}+, req {{ $queryOffset + 1 }})</span>
                                @endif
                            @else
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded">En attente</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
