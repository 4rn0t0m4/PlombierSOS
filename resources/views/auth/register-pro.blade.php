<x-layouts.app title="Inscription Professionnel - Plombier SOS">
    <div class="max-w-2xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Inscription Professionnel</h1>
        <p class="text-gray-600 mb-8">Inscrivez votre établissement sur Plombier SOS. Votre fiche sera vérifiée par notre équipe avant publication.</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.pro.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Compte --}}
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Votre compte</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom / Prénom *</label>
                        <input type="text" name="name" required value="{{ old('name') }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email *</label>
                        <input type="email" name="email" required value="{{ old('email') }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Mot de passe *</label>
                        <input type="password" name="password" required minlength="8" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Confirmer le mot de passe *</label>
                        <input type="password" name="password_confirmation" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>

            {{-- Établissement --}}
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Votre établissement</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom de l'entreprise *</label>
                        <input type="text" name="title" required value="{{ old('title') }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Type d'activité *</label>
                        <select name="type" required class="w-full border rounded-lg px-3 py-2">
                            <option value="0" {{ old('type') == '0' ? 'selected' : '' }}>Plombier</option>
                            <option value="1" {{ old('type') == '1' ? 'selected' : '' }}>Chauffagiste</option>
                            <option value="2" {{ old('type') == '2' ? 'selected' : '' }}>Plombier-Chauffagiste</option>
                            <option value="3" {{ old('type') == '3' ? 'selected' : '' }}>Dépanneur urgence</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Téléphone *</label>
                        <input type="tel" name="phone" required value="{{ old('phone') }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">SIRET</label>
                        <input type="text" name="siret" value="{{ old('siret') }}" maxlength="14" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>

            {{-- Adresse --}}
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Adresse</h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Adresse *</label>
                    <input type="text" name="address" required value="{{ old('address') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Code postal *</label>
                        <input type="text" name="postal_code" required maxlength="5" value="{{ old('postal_code') }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ville *</label>
                        <input type="text" name="city" required value="{{ old('city') }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Rayon d'intervention (km)</label>
                        <input type="number" name="service_radius" value="{{ old('service_radius', 20) }}" min="1" max="100" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>

            {{-- Services --}}
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Services</h2>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="emergency_24h" value="1" {{ old('emergency_24h') ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-blue-600">
                        <span>Disponible en urgence 24h/24</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="free_quote" value="1" {{ old('free_quote') ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-blue-600">
                        <span>Devis gratuit</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="rge_certified" value="1" {{ old('rge_certified') ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-blue-600">
                        <span>Certifié RGE</span>
                    </label>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Description de votre activité</label>
                    <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2" placeholder="Présentez votre activité, vos spécialités...">{{ old('description') }}</textarea>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-900 text-white font-semibold py-3 rounded-lg hover:bg-blue-800 cursor-pointer">S'inscrire</button>

            <p class="text-sm text-gray-500 text-center">Déjà inscrit ? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Se connecter</a></p>
        </form>
    </div>
</x-layouts.app>
