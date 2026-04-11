<x-layouts.app title="Demander un devis plombier - Plombier SOS">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Demande d'intervention</h1>
        <form method="POST" action="{{ route('demande.store') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            @if($plombier)
                <input type="hidden" name="plombier_id" value="{{ $plombier->id }}">
                <p class="text-sm text-gray-500 mb-4">Demande pour : <strong>{{ $plombier->titre }}</strong></p>
            @endif
            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div><label class="block text-sm font-medium mb-1">Nom *</label><input type="text" name="nom" required class="w-full border rounded-lg px-3 py-2" value="{{ old('nom', auth()->user()?->pseudo) }}"></div>
                <div><label class="block text-sm font-medium mb-1">Email *</label><input type="email" name="email" required class="w-full border rounded-lg px-3 py-2" value="{{ old('email', auth()->user()?->email) }}"></div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div><label class="block text-sm font-medium mb-1">Téléphone *</label><input type="tel" name="telephone" required class="w-full border rounded-lg px-3 py-2" value="{{ old('telephone') }}"></div>
                <div><label class="block text-sm font-medium mb-1">Code postal *</label><input type="text" name="cp" required maxlength="5" class="w-full border rounded-lg px-3 py-2" value="{{ old('cp') }}"></div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Type d'intervention *</label>
                    <select name="type" required class="w-full border rounded-lg px-3 py-2">
                        <option value="depannage">Dépannage</option>
                        <option value="installation">Installation</option>
                        <option value="entretien">Entretien</option>
                        <option value="devis">Devis</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Urgence *</label>
                    <select name="urgence" required class="w-full border rounded-lg px-3 py-2">
                        <option value="normale">Normale</option>
                        <option value="urgente">Urgente</option>
                        <option value="tres_urgente">Très urgente</option>
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
