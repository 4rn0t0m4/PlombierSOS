<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Plombier SOS - Trouvez un plombier en urgence' }}</title>
    <meta name="description" content="{{ $description ?? 'Trouvez un plombier, chauffagiste ou dépanneur près de chez vous. Urgence 24h/24, devis gratuit, avis clients.' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-gray-50 flex flex-col" x-data>
    {{-- Header --}}
    <header class="bg-blue-900 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('home') }}" class="text-xl font-bold flex items-center gap-2">
                    <svg class="w-7 h-7 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.66 11.2c-.23-.3-.51-.56-.77-.82-.67-.6-1.43-1.03-2.07-1.66C13.33 7.26 13 4.85 13.95 3c-.95.23-1.78.75-2.49 1.32-2.59 2.08-3.61 5.75-2.39 8.9.04.1.08.2.08.33 0 .22-.15.42-.35.5-.23.1-.47.04-.66-.12a.58.58 0 01-.14-.17c-1.13-1.43-1.31-3.48-.55-5.12C5.78 10 4.87 12.3 5 14.47c.06.5.12 1 .29 1.5.14.6.41 1.2.71 1.73 1.08 1.73 2.95 2.97 4.96 3.22 2.14.27 4.43-.12 6.07-1.6 1.83-1.66 2.47-4.32 1.53-6.6l-.13-.26c-.21-.46-.77-1.26-.77-1.26m-3.16 6.3c-.28.24-.74.5-1.1.6-1.12.4-2.24-.16-2.9-.82 1.19-.28 1.9-1.16 2.11-2.05.17-.8-.15-1.46-.28-2.23-.12-.74-.1-1.37.17-2.06.19.38.39.76.63 1.06.77 1 1.98 1.44 2.24 2.8.04.14.06.28.06.43.03.82-.33 1.72-.93 2.27z"/></svg>
                    Plombier SOS
                </a>

                <nav class="hidden md:flex items-center gap-6 text-sm">
                    <a href="{{ route('home') }}" class="hover:text-red-300 transition">Accueil</a>
                    <a href="{{ route('recherche') }}" class="hover:text-red-300 transition">Rechercher</a>
                    <a href="{{ route('urgence') }}" class="bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg font-semibold transition">Urgence 24h</a>
                    <a href="{{ route('demande.create') }}" class="hover:text-red-300 transition">Demander un devis</a>
                </nav>

                <div class="flex items-center gap-4 text-sm">
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-red-300">Admin</a>
                        @endif
                        <a href="{{ route('logout') }}" class="hover:text-red-300">Déconnexion</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-red-300">Connexion</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 mt-12 py-10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="font-semibold text-white mb-3">Plombier SOS</h3>
                    <p class="text-sm">Trouvez un plombier de confiance près de chez vous. Urgence 24h/24, 7j/7.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-3">Liens utiles</h3>
                    <ul class="text-sm space-y-1">
                        <li><a href="{{ route('recherche') }}" class="hover:text-white">Rechercher un plombier</a></li>
                        <li><a href="{{ route('urgence') }}" class="hover:text-white">Urgence plomberie</a></li>
                        <li><a href="{{ route('demande.create') }}" class="hover:text-white">Demander un devis</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-3">Informations</h3>
                    <ul class="text-sm space-y-1">
                        <li><a href="{{ route('mentions-legales') }}" class="hover:text-white">Mentions légales</a></li>
                        <li><a href="{{ route('confidentialite') }}" class="hover:text-white">Confidentialité</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-4 text-sm text-center">
                &copy; {{ date('Y') }} Plombier SOS. Tous droits réservés.
            </div>
        </div>
    </footer>
    @stack('jsonld')
</body>
</html>
