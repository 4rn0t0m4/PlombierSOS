@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Réclamations de fiches ({{ $claims->total() }})</h1>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Date</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Demandeur</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Établissement</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Rôle</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Statut</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($claims as $claim)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $claim->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $claim->name }}</div>
                            <div class="text-xs text-gray-400">{{ $claim->email }}</div>
                            <div class="text-xs text-gray-400">{{ $claim->phone }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ $claim->plumber->url }}" target="_blank" class="text-blue-600 hover:underline">{{ $claim->plumber->title }}</a>
                            <div class="text-xs text-gray-400">{{ $claim->plumber->city }} ({{ $claim->plumber->postal_code }})</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @php
                                $roles = ['owner' => 'Gérant', 'manager' => 'Responsable', 'employee' => 'Employé'];
                            @endphp
                            {{ $roles[$claim->role] ?? $claim->role }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'];
                                $statusLabels = ['pending' => 'En attente', 'approved' => 'Approuvée', 'rejected' => 'Refusée'];
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded {{ $statusColors[$claim->status] }}">{{ $statusLabels[$claim->status] }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($claim->status === 'pending')
                                <div x-data="{ showForm: false }" class="inline">
                                    <button @click="showForm = !showForm" class="text-blue-600 hover:underline text-xs cursor-pointer">Traiter</button>
                                    <div x-show="showForm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                        <div class="fixed inset-0 bg-black/50" @click="showForm = false"></div>
                                        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 z-10" @click.stop>
                                            <h3 class="font-bold mb-3">Traiter la réclamation</h3>
                                            <p class="text-sm text-gray-600 mb-2"><strong>{{ $claim->plumber->title }}</strong> par {{ $claim->name }}</p>
                                            @if($claim->message)
                                                <div class="bg-gray-50 rounded p-3 text-sm text-gray-700 mb-3">{{ $claim->message }}</div>
                                            @endif
                                            <form action="{{ route('admin.reclamations.update', $claim) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium mb-1">Notes admin</label>
                                                    <textarea name="admin_notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                                                </div>
                                                <div class="flex gap-2">
                                                    <button type="submit" name="status" value="approved" class="flex-1 bg-green-600 text-white text-sm font-semibold py-2 rounded-lg hover:bg-green-700 cursor-pointer">Approuver</button>
                                                    <button type="submit" name="status" value="rejected" class="flex-1 bg-red-600 text-white text-sm font-semibold py-2 rounded-lg hover:bg-red-700 cursor-pointer">Refuser</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">{{ $statusLabels[$claim->status] }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune réclamation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $claims->links() }}</div>
@endsection
