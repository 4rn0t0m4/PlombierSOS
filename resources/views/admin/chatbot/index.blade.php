@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Conversations chatbot ({{ $conversations->total() }})</h1>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Date</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Localisation</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Page</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Messages</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Dernier message</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($conversations as $conv)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $conv->updated_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @if($conv->city)
                                <span class="font-medium">{{ $conv->city }}</span>
                                @if($conv->postal_code)
                                    <span class="text-gray-400">({{ $conv->postal_code }})</span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs max-w-48 truncate">{{ $conv->page_url ? parse_url($conv->page_url, PHP_URL_PATH) : '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $conv->message_count }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 max-w-64 truncate">
                            @php
                                $lastUserMsg = collect($conv->messages)->where('role', 'user')->last();
                            @endphp
                            {{ $lastUserMsg['content'] ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.chatbot.show', $conv) }}" class="text-blue-600 hover:underline text-xs mr-2">Voir</a>
                            <form action="{{ route('admin.chatbot.destroy', $conv) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline text-xs">Suppr.</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune conversation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $conversations->links() }}</div>
@endsection
