@extends('admin.layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="{{ route('admin.chatbot.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Retour</a>
            <h1 class="text-2xl font-bold mt-2">Conversation #{{ $conversation->id }}</h1>
        </div>
        <div class="text-sm text-gray-500 text-right">
            <p>{{ $conversation->created_at->format('d/m/Y H:i') }}</p>
            @if($conversation->city)
                <p>{{ $conversation->city }} {{ $conversation->postal_code ? '('.$conversation->postal_code.')' : '' }}</p>
            @endif
            @if($conversation->page_url)
                <p class="text-xs">{{ $conversation->page_url }}</p>
            @endif
            <p class="text-xs text-gray-400">IP: {{ $conversation->ip }}</p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto space-y-4">
        @foreach($conversation->messages as $msg)
            <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="{{ $msg['role'] === 'user' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }} rounded-2xl px-4 py-3 max-w-lg text-sm">
                    <p class="text-xs font-semibold mb-1 {{ $msg['role'] === 'user' ? 'text-blue-200' : 'text-gray-400' }}">
                        {{ $msg['role'] === 'user' ? 'Visiteur' : 'Assistant' }}
                    </p>
                    <div>{!! nl2br(e($msg['content'])) !!}</div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
