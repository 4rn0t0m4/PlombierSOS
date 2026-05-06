@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Messages ({{ $messages->total() }})</h1>

    <div class="space-y-4">
        @forelse($messages as $message)
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="font-semibold text-gray-900">{{ $message->name }}</span>
                            <span class="text-xs text-gray-400">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="text-xs text-gray-500 mb-2">
                            {{ $message->email }}
                            @if($message->phone) - {{ $message->phone }} @endif
                            @if($message->subject)
                                — <span class="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">{{ $message->subject }}</span>
                            @endif
                            @if($message->plumber)
                                — pour <a href="{{ route('admin.plombiers.edit', $message->plumber) }}" class="text-blue-600 hover:underline">{{ $message->plumber->title }}</a>
                            @endif
                        </div>
                        <p class="text-sm text-gray-700">{{ $message->content }}</p>
                    </div>
                    <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Supprimer ce message ?')">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:underline text-xs">Suppr.</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-gray-500">Aucun message.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $messages->links() }}</div>
@endsection
