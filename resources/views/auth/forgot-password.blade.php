<x-layouts.app title="Mot de passe oublié - Plombier SOS">
    <div class="max-w-md mx-auto px-4 py-12">
        <h1 class="text-2xl font-bold mb-6 text-center">Mot de passe oublié</h1>
        <form method="POST" action="{{ route('password.email') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            <p class="text-sm text-gray-500 mb-4">Entrez votre email pour recevoir un lien de réinitialisation.</p>
            <div class="mb-4"><label class="block text-sm font-medium mb-1">Email</label><input type="email" name="email" required class="w-full border rounded-lg px-3 py-2"></div>
            <button type="submit" class="w-full bg-blue-900 text-white py-2 rounded-lg hover:bg-blue-800">Envoyer le lien</button>
        </form>
    </div>
</x-layouts.app>
