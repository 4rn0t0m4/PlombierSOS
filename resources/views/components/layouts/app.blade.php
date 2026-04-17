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
    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $title ?? 'Plombier SOS' }}">
    <meta property="og:description" content="{{ $description ?? 'Trouvez un plombier de confiance près de chez vous. Urgence 24h/24.' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Plombier SOS">
    <meta property="og:locale" content="fr_FR">
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title ?? 'Plombier SOS' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Trouvez un plombier de confiance près de chez vous.' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Google Analytics --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-75PPQJKWBL"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-75PPQJKWBL');</script>
    @stack('head')
</head>
<body class="min-h-screen bg-gray-50 flex flex-col" x-data="{ mobileMenu: false }">
    {{-- Header --}}
    <header class="bg-blue-900 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('home') }}" class="text-xl font-bold flex items-center gap-2" aria-label="Plombier SOS - Accueil">
                    <svg class="w-7 h-7 text-red-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.66 11.2c-.23-.3-.51-.56-.77-.82-.67-.6-1.43-1.03-2.07-1.66C13.33 7.26 13 4.85 13.95 3c-.95.23-1.78.75-2.49 1.32-2.59 2.08-3.61 5.75-2.39 8.9.04.1.08.2.08.33 0 .22-.15.42-.35.5-.23.1-.47.04-.66-.12a.58.58 0 01-.14-.17c-1.13-1.43-1.31-3.48-.55-5.12C5.78 10 4.87 12.3 5 14.47c.06.5.12 1 .29 1.5.14.6.41 1.2.71 1.73 1.08 1.73 2.95 2.97 4.96 3.22 2.14.27 4.43-.12 6.07-1.6 1.83-1.66 2.47-4.32 1.53-6.6l-.13-.26c-.21-.46-.77-1.26-.77-1.26m-3.16 6.3c-.28.24-.74.5-1.1.6-1.12.4-2.24-.16-2.9-.82 1.19-.28 1.9-1.16 2.11-2.05.17-.8-.15-1.46-.28-2.23-.12-.74-.1-1.37.17-2.06.19.38.39.76.63 1.06.77 1 1.98 1.44 2.24 2.8.04.14.06.28.06.43.03.82-.33 1.72-.93 2.27z"/></svg>
                    Plombier SOS
                </a>

                {{-- Desktop nav --}}
                <nav class="hidden md:flex items-center gap-6 text-sm">
                    <a href="{{ route('home') }}" class="hover:text-red-300 transition">Accueil</a>
                    <a href="{{ route('recherche') }}" class="hover:text-red-300 transition">Rechercher</a>
                    <a href="{{ route('urgence') }}" class="bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg font-semibold transition">Urgence 24h</a>
                    <a href="{{ route('demande.create') }}" class="hover:text-red-300 transition">Demander un devis</a>
                </nav>

                <div class="flex items-center gap-4 text-sm">
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-red-300 hidden sm:inline">Admin</a>
                        @endif
                        <a href="{{ route('logout') }}" class="hover:text-red-300 hidden sm:inline">Deconnexion</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-red-300 hidden sm:inline">Connexion</a>
                    @endauth

                    {{-- Hamburger button --}}
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-1" aria-label="Menu">
                        <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        <svg x-show="mobileMenu" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile menu --}}
        <nav x-show="mobileMenu" x-cloak x-transition class="md:hidden border-t border-blue-800 pb-4">
            <div class="max-w-7xl mx-auto px-4 pt-3 space-y-2 text-sm">
                <a href="{{ route('home') }}" class="block py-2 hover:text-red-300">Accueil</a>
                <a href="{{ route('recherche') }}" class="block py-2 hover:text-red-300">Rechercher un plombier</a>
                <a href="{{ route('urgence') }}" class="block py-2 text-red-400 font-semibold">Urgence 24h/24</a>
                <a href="{{ route('demande.create') }}" class="block py-2 hover:text-red-300">Demander un devis</a>
                <div class="border-t border-blue-800 pt-2 mt-2">
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="block py-2 hover:text-red-300">Admin</a>
                        @endif
                        <a href="{{ route('logout') }}" class="block py-2 hover:text-red-300">Deconnexion</a>
                    @else
                        <a href="{{ route('login') }}" class="block py-2 hover:text-red-300">Connexion</a>
                    @endauth
                </div>
            </div>
        </nav>
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
    <main class="flex-1" id="main">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 mt-12 py-10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-8">
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
    {{-- Chatbot IA --}}
    <div x-data="chatbot()" class="fixed bottom-6 right-6 z-50">
        {{-- Chat window --}}
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="mb-4 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border flex flex-col" style="height: 480px;">
            {{-- Header --}}
            <div class="bg-blue-900 text-white px-4 py-3 rounded-t-2xl flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/></svg>
                    <span class="font-semibold text-sm">Assistant Plombier SOS</span>
                </div>
                <button @click="toggle()" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Messages --}}
            <div x-ref="messages" class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.role === 'user' ? 'bg-blue-600 text-white rounded-2xl rounded-br-md' : 'bg-gray-100 text-gray-800 rounded-2xl rounded-bl-md'" class="px-4 py-2 max-w-[85%] text-sm" x-html="formatMessage(msg.content)">
                        </div>
                    </div>
                </template>
                <div x-show="loading" class="flex justify-start">
                    <div class="bg-gray-100 rounded-2xl rounded-bl-md px-4 py-2 text-sm text-gray-400">
                        <span class="inline-flex gap-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Input --}}
            <div class="border-t p-3">
                <form @submit.prevent="send()" class="flex gap-2">
                    <input x-ref="chatInput" x-model="input" type="text" placeholder="Décrivez votre problème..." class="flex-1 border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :disabled="loading">
                    <button type="submit" :disabled="loading || !input.trim()" class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white rounded-xl px-3 py-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Floating button --}}
        <button @click="toggle()" class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition hover:scale-110" :class="open ? 'hidden' : ''">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
        </button>
    </div>

    @stack('jsonld')
</body>
</html>
