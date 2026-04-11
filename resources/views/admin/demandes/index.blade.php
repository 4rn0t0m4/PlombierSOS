@extends('admin.layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Demandes d'intervention ({{ $demandes->total() }})</h1>
    </div>

    <div class="flex gap-2 mb-4">
        <a href="{{ route('admin.demandes.index') }}" class="text-sm px-3 py-1 rounded {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Toutes</a>
        @foreach(['new', 'sent', 'accepted', 'refused', 'completed'] as $status)
            <a href="{{ route('admin.demandes.index', ['status' => $status]) }}" class="text-sm px-3 py-1 rounded {{ request('status') === $status ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">{{ ucfirst($status) }}</a>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Date</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Client</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Plombier</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Urgence</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Statut</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($demandes as $demande)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $demande->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $demande->name }}</div>
                            <div class="text-xs text-gray-400">{{ $demande->email }} - {{ $demande->city }} ({{ $demande->postal_code }})</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $demande->plumber?->title ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $colors = ['normal' => 'bg-gray-100 text-gray-700', 'urgent' => 'bg-orange-100 text-orange-700', 'very_urgent' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded {{ $colors[$demande->urgency] ?? 'bg-gray-100 text-gray-700' }}">{{ $demande->urgency }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = ['new' => 'bg-blue-100 text-blue-700', 'sent' => 'bg-yellow-100 text-yellow-700', 'accepted' => 'bg-green-100 text-green-700', 'refused' => 'bg-red-100 text-red-700', 'completed' => 'bg-gray-100 text-gray-700'];
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded {{ $statusColors[$demande->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $demande->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.demandes.show', $demande) }}" class="text-blue-600 hover:underline text-xs mr-2">Détail</a>
                            <form action="{{ route('admin.demandes.destroy', $demande) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette demande ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline text-xs">Suppr.</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune demande.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $demandes->links() }}</div>
@endsection
