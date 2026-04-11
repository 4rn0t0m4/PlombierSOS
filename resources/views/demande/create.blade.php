<x-layouts.app title="Demander un devis plombier - Plombier SOS">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Demande d'intervention</h1>
        <form method="POST" action="{{ route('demande.store') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            @if($plombier)
                <input type="hidden" name="plumber_id" value="{{ $plombier->id }}">
                <p class="text-sm text-gray-500 mb-4">Demande pour : <strong>{{ $plombier->title }}</strong></p>
            @endif
            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div><label class="block text-sm font-medium mb-1">Nom *</label><input type="text" name="name" required class="w-full border rounded-lg px-3 py-2" value="{{ old('name', auth()->user()?->username) }}"></div>
                <div><label class="block text-sm font-medium mb-1">Email *</label><input type="email" name="email" required class="w-full border rounded-lg px-3 py-2" value="{{ old('email', auth()->user()?->email) }}"></div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div><label class="block text-sm font-medium mb-1">Téléphone *</label><input type="tel" name="phone" required class="w-full border rounded-lg px-3 py-2" value="{{ old('phone') }}"></div>
                <div><label class="block text-sm font-medium mb-1">Code postal *</label><input type="text" name="postal_code" required maxlength="5" class="w-full border rounded-lg px-3 py-2" value="{{ old('postal_code') }}"></div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Type d'intervention *</label>
                    <select name="type" required class="w-full border rounded-lg px-3 py-2">
                        <option value="repair">Dépannage</option>
                        <option value="installation">Installation</option>
                        <option value="maintenance">Entretien</option>
                        <option value="quote">Devis</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Urgence *</label>
                    <select name="urgency" required class="w-full border rounded-lg px-3 py-2">
                        <option value="normal">Normale</option>
                        <option value="urgent">Urgente</option>
                        <option value="very_urgent">Très urgente</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Description du problème *</label>
                <textarea name="description" rows="5" required class="w-full border rounded-lg px-3 py-2" placeholder="Décrivez votre problème de plomberie...">{{ old('description') }}</textarea>
            </div>
            <button type="submit" class="w-full bg-blue-900 text-white font-semibold py-3 rounded-lg hover:bg-blue-800">Envoyer ma demande</button>
        </form>
    </div>
</x-layouts.app>
