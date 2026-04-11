@extends('admin.layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Plombiers ({{ $plombiers->total() }})</h1>
    </div>

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-gray-500 mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, ville, CP..." class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Type</label>
            <select name="type" class="border rounded px-3 py-2 text-sm">
                <option value="">Tous</option>
                @foreach(\App\Models\Plumber::TYPE_LABELS as $val => $label)
                    <option value="{{ $val }}" {{ request('type') == (string)$val && request('type') !== '' ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Statut</label>
            <select name="is_active" class="border rounded px-3 py-2 text-sm">
                <option value="">Tous</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Validés</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Non validés</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Dept</label>
            <input type="text" name="department" value="{{ request('department') }}" placeholder="75" class="w-16 border rounded px-3 py-2 text-sm">
        </div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Filtrer</button>
        @if(request()->hasAny(['search', 'type', 'is_active', 'department']))
            <a href="{{ route('admin.plombiers.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Effacer</a>
        @endif
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Plombier</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Localisation</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Type</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Note</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Statut</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($plombiers as $plombier)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.plombiers.edit', $plombier) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $plombier->title }}</a>
                            <div class="text-xs text-gray-400">{{ $plombier->phone }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $plombier->city }} ({{ $plombier->postal_code }})</td>
                        <td class="px-4 py-3">
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">{{ $plombier->type_label }}</span>
                            @if($plombier->emergency_24h)
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded ml-1">24h</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($plombier->google_rating)
                                <span class="text-yellow-500 font-medium">{{ $plombier->google_rating }}</span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('admin.plombiers.toggle-valide', $plombier) }}" method="POST" class="inline">
                                @csrf
                                <button class="text-xs px-2 py-0.5 rounded {{ $plombier->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                    {{ $plombier->is_active ? 'Validé' : 'Inactif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.plombiers.edit', $plombier) }}" class="text-blue-600 hover:underline text-xs mr-2">Modifier</a>
                            <a href="{{ $plombier->url }}" target="_blank" class="text-gray-400 hover:underline text-xs mr-2">Voir</a>
                            <form action="{{ route('admin.plombiers.destroy', $plombier) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce plombier ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline text-xs">Suppr.</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucun plombier trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $plombiers->links() }}</div>
@endsection
