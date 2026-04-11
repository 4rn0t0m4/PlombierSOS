<x-layouts.app title="Inscription - Plombier SOS">
    <div class="max-w-md mx-auto px-4 py-12">
        <h1 class="text-2xl font-bold mb-6 text-center">Créer un compte</h1>
        <form method="POST" action="{{ route('register') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            <div class="mb-4"><label class="block text-sm font-medium mb-1">Pseudo</label><input type="text" name="username" required class="w-full border rounded-lg px-3 py-2" value="{{ old('username') }}"></div>
            <div class="mb-4"><label class="block text-sm font-medium mb-1">Email</label><input type="email" name="email" required class="w-full border rounded-lg px-3 py-2" value="{{ old('email') }}"></div>
            <div class="mb-4"><label class="block text-sm font-medium mb-1">Mot de passe</label><input type="password" name="password" required class="w-full border rounded-lg px-3 py-2"></div>
            <div class="mb-4"><label class="block text-sm font-medium mb-1">Confirmer le mot de passe</label><input type="password" name="password_confirmation" required class="w-full border rounded-lg px-3 py-2"></div>
            <button type="submit" class="w-full bg-blue-900 text-white py-2 rounded-lg hover:bg-blue-800">S'inscrire</button>
        </form>
    </div>
</x-layouts.app>
