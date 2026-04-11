<x-layouts.app title="Connexion - Plombier SOS">
    <div class="max-w-md mx-auto px-4 py-12">
        <h1 class="text-2xl font-bold mb-6 text-center">Connexion</h1>
        <form method="POST" action="{{ route('login') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            <div class="mb-4"><label class="block text-sm font-medium mb-1">Email</label><input type="email" name="email" required class="w-full border rounded-lg px-3 py-2" value="{{ old('email') }}"></div>
            <div class="mb-4"><label class="block text-sm font-medium mb-1">Mot de passe</label><input type="password" name="password" required class="w-full border rounded-lg px-3 py-2"></div>
            <div class="mb-4 flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="rounded"> Se souvenir</label>
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">Mot de passe oublié ?</a>
            </div>
            <button type="submit" class="w-full bg-blue-900 text-white py-2 rounded-lg hover:bg-blue-800">Se connecter</button>
        </form>
        <p class="text-center text-sm text-gray-500 mt-4">Pas de compte ? <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Créer un compte</a></p>
    </div>
</x-layouts.app>
