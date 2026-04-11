<x-layouts.app title="Page introuvable - Plombier SOS">
    <div class="max-w-4xl mx-auto px-4 py-16 text-center">
        <p class="text-8xl font-bold text-blue-200 mb-4">404</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Page introuvable</h1>
        <p class="text-gray-500 mb-8">La page que vous cherchez n'existe pas.</p>
        <div class="flex justify-center gap-4">
            <a href="{{ route('home') }}" class="bg-blue-900 text-white px-6 py-2 rounded-lg hover:bg-blue-800">Accueil</a>
            <a href="{{ route('recherche') }}" class="border border-blue-900 text-blue-900 px-6 py-2 rounded-lg hover:bg-blue-50">Rechercher</a>
        </div>
    </div>
</x-layouts.app>
