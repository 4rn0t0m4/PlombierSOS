@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Avis en attente de modération</h1>
    @if($avis->isEmpty())
        <p class="text-gray-500">Aucun avis en attente.</p>
    @else
        @foreach($avis as $a)
            <div class="bg-white rounded-lg shadow-sm border p-4 mb-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold">{{ $a->title }}</p>
                        <p class="text-sm text-gray-500">par {{ $a->user?->username ?? $a->author_username ?? 'Anonyme' }} — {{ $a->plumber->title }}</p>
                        <p class="text-sm text-gray-700 mt-1">{{ Str::limit($a->content, 200) }}</p>
                    </div>
                    <div class="flex gap-2">
                        <form action="{{ route('admin.avis.moderer', $a) }}" method="POST">@csrf<input type="hidden" name="action" value="valider"><button class="bg-green-600 text-white text-xs px-3 py-1 rounded hover:bg-green-700">Valider</button></form>
                        <form action="{{ route('admin.avis.moderer', $a) }}" method="POST">@csrf<input type="hidden" name="action" value="refuser"><button class="bg-red-600 text-white text-xs px-3 py-1 rounded hover:bg-red-700">Refuser</button></form>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="mt-4">{{ $avis->links() }}</div>
    @endif
@endsection
