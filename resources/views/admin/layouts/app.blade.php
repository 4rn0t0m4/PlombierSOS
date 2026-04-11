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
            <nav class="space-y-2 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-blue-800">Dashboard</a>
                <a href="{{ route('admin.avis.index') }}" class="block px-3 py-2 rounded hover:bg-blue-800">Avis</a>
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded hover:bg-blue-800 mt-8 text-blue-300">← Retour au site</a>
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
