<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - Plombier SOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen" x-data>
    <div class="flex">
        <aside class="w-64 bg-blue-900 text-white min-h-screen p-6">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold block mb-8">Plombier SOS</a>
            <nav class="space-y-1 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-blue-800 text-white' : 'hover:bg-blue-800' }}">Dashboard</a>
                <a href="{{ route('admin.plombiers.index') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.plombiers.*') ? 'bg-blue-800 text-white' : 'hover:bg-blue-800' }}">Plombiers</a>
                <a href="{{ route('admin.avis.index') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.avis.*') ? 'bg-blue-800 text-white' : 'hover:bg-blue-800' }}">Avis</a>
                <a href="{{ route('admin.chatbot.index') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.chatbot.*') ? 'bg-blue-800 text-white' : 'hover:bg-blue-800' }}">Chatbot</a>
                @php
                    $pendingDemandes = \App\Models\ServiceRequest::where('status', 'new')->count();
                    $pendingClaims = \App\Models\ClaimRequest::where('status', 'pending')->count();
                @endphp
                <a href="{{ route('admin.demandes.index') }}" class="flex items-center justify-between px-3 py-2 rounded {{ request()->routeIs('admin.demandes.*') ? 'bg-blue-800 text-white' : 'hover:bg-blue-800' }}">
                    Demandes
                    @if($pendingDemandes)
                        <span class="bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $pendingDemandes }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.messages.index') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.messages.*') ? 'bg-blue-800 text-white' : 'hover:bg-blue-800' }}">Messages</a>
                <a href="{{ route('admin.reclamations.index') }}" class="flex items-center justify-between px-3 py-2 rounded {{ request()->routeIs('admin.reclamations.*') ? 'bg-blue-800 text-white' : 'hover:bg-blue-800' }}">
                    Réclamations
                    @if($pendingClaims)
                        <span class="bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $pendingClaims }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.stats') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.stats') ? 'bg-blue-800 text-white' : 'hover:bg-blue-800' }}">Statistiques</a>
                <div class="border-t border-blue-800 my-4"></div>
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded hover:bg-blue-800 text-blue-300">← Retour au site</a>
                <a href="{{ route('logout') }}" class="block px-3 py-2 rounded hover:bg-blue-800 text-blue-300">Déconnexion</a>
            </nav>
        </aside>
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
