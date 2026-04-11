@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border p-6"><p class="text-sm text-gray-500">Plombiers</p><p class="text-3xl font-bold text-gray-900">{{ $stats['plombiers'] }}</p></div>
        <div class="bg-white rounded-lg shadow-sm border p-6"><p class="text-sm text-gray-500">Validés</p><p class="text-3xl font-bold text-green-600">{{ $stats['plombiers_valides'] }}</p></div>
        <div class="bg-white rounded-lg shadow-sm border p-6"><p class="text-sm text-gray-500">Avis en attente</p><p class="text-3xl font-bold text-orange-500">{{ $stats['avis_en_attente'] }}</p></div>
        <div class="bg-white rounded-lg shadow-sm border p-6"><p class="text-sm text-gray-500">Nouvelles demandes</p><p class="text-3xl font-bold text-blue-600">{{ $stats['demandes_nouvelles'] }}</p></div>
    </div>
@endsection
