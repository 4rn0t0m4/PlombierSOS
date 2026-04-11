<x-layouts.app title="Réinitialiser le mot de passe - Plombier SOS">
    <div class="max-w-md mx-auto px-4 py-12">
        <h1 class="text-2xl font-bold mb-6 text-center">Nouveau mot de passe</h1>
        <form method="POST" action="{{ route('password.update') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-4"><label class="block text-sm font-medium mb-1">Email</label><input type="email" name="email" required class="w-full border rounded-lg px-3 py-2"></div>
            <div class="mb-4"><label class="block text-sm font-medium mb-1">Nouveau mot de passe</label><input type="password" name="password" required class="w-full border rounded-lg px-3 py-2"></div>
            <div class="mb-4"><label class="block text-sm font-medium mb-1">Confirmer</label><input type="password" name="password_confirmation" required class="w-full border rounded-lg px-3 py-2"></div>
            <button type="submit" class="w-full bg-blue-900 text-white py-2 rounded-lg hover:bg-blue-800">Réinitialiser</button>
        </form>
    </div>
</x-layouts.app>
