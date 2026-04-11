<x-layouts.app
    :title="$plombier->titre . ' - ' . $plombier->type_label . ' à ' . $plombier->ville . ' - Plombier SOS'"
    :description="$plombier->titre . ', ' . strtolower($plombier->type_label) . ' à ' . $plombier->ville . ($plombier->google_rating ? '. Note Google : ' . $plombier->google_rating . '/5' : '') . '. Téléphone, horaires, avis.'"
>
    @if($plombier->latitude && $plombier->longitude)
        @push('head')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css" />
        @endpush
    @endif

    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Accueil</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $plombier->titre }}</span>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <h1 class="text-3xl font-bold text-gray-900">{{ $plombier->titre }}</h1>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-blue-600">{{ $plombier->type_label }}</span>
                    <x-statut-ouverture :plombier="$plombier" />
                </div>

                @if($plombier->google_rating)
                    <div class="flex items-center gap-2 mt-3">
                        <x-star-rating :rating="$plombier->google_rating" />
                        <span class="font-semibold">{{ number_format($plombier->google_rating, 1, ',', '') }}/5</span>
                        <span class="text-gray-500">({{ $plombier->google_nb_avis }} avis Google)</span>
                    </div>
                @endif

                @if($plombier->classement_ville > 0 && $totalInVille > 1)
                    <p class="text-sm text-gray-600 mt-2">
                        Classé <span class="font-semibold text-blue-600">n°{{ $plombier->classement_ville }}</span>
                        sur {{ $totalInVille }} plombiers à {{ $plombier->ville }}
                    </p>
                @endif

                <div class="flex gap-2 mt-3">
                    @if($plombier->urgence_24h)
                        <span class="text-sm bg-red-100 text-red-700 px-2 py-1 rounded">Urgence 24h/24</span>
                    @endif
                    @if($plombier->devis_gratuit)
                        <span class="text-sm bg-green-100 text-green-700 px-2 py-1 rounded">Devis gratuit</span>
                    @endif
                    @if($plombier->agree_rge)
                        <span class="text-sm bg-blue-100 text-blue-700 px-2 py-1 rounded">Certifié RGE</span>
                    @endif
                </div>

                {{-- Coordonnées --}}
                <div class="mt-8 bg-white border rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Coordonnées</h2>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            @if($plombier->adresse)
                                <div>
                                    <p class="text-sm text-gray-500">Adresse</p>
                                    <p class="text-sm font-medium">{{ $plombier->adresse }}<br>{{ $plombier->cp }} {{ $plombier->ville }}</p>
                                </div>
                            @endif
                            @if($plombier->telephone)
                                <x-phone-reveal :phone="$plombier->telephone" :plombier-id="$plombier->id" />
                            @endif
                            <div class="pt-2">
                                <button @click="$store.contactModal.open = true" type="button" class="w-full flex items-center justify-center gap-2 bg-blue-900 text-white font-semibold py-3 px-5 rounded-lg hover:bg-blue-800 transition cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                    Contacter
                                </button>
                            </div>
                            @if($plombier->site_web)
                                <a href="{{ $plombier->site_web }}" target="_blank" class="block text-sm text-blue-600 hover:underline break-all">{{ $plombier->site_web }}</a>
                            @endif
                        </div>
                        @if($plombier->latitude && $plombier->longitude)
                            <div class="rounded-lg overflow-hidden border" id="map" style="min-height: 220px;"></div>
                        @endif
                    </div>
                </div>

                @if($plombier->description)
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Présentation</h2>
                        <div class="prose text-gray-700">{!! nl2br(e($plombier->description)) !!}</div>
                    </div>
                @endif

                @if($plombier->horairesRelation->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Horaires</h2>
                        <table class="w-full text-sm">
                            @foreach($plombier->horairesRelation as $h)
                                <tr class="border-b">
                                    <td class="py-2 font-medium">{{ $h->jour_label }}</td>
                                    <td class="py-2 text-gray-600">
                                        @if($h->ferme) <span class="text-red-500">Fermé</span>
                                        @else
                                            {{ $h->matin_ouverture ? substr($h->matin_ouverture, 0, 5) . ' - ' . substr($h->matin_fermeture, 0, 5) : '' }}
                                            @if($h->aprem_ouverture) / {{ substr($h->aprem_ouverture, 0, 5) }} - {{ substr($h->aprem_fermeture, 0, 5) }} @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif

                {{-- Avis --}}
                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-3">Avis ({{ $plombier->approvedAvis->count() }})</h2>

                    @foreach($plombier->approvedAvis as $avis)
                        <div class="bg-white border rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-semibold">{{ $avis->auteur_name }}</span>
                                    <span class="text-sm text-gray-400 ml-2">{{ $avis->created_at->format('d/m/Y') }}</span>
                                    @if($avis->type_intervention)
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded ml-2">{{ $avis->type_intervention }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-star-rating :rating="$avis->moyenne" size="w-4 h-4" />
                                    <span class="text-sm font-semibold">{{ number_format($avis->moyenne, 1, ',', '') }}</span>
                                </div>
                            </div>
                            <h3 class="font-medium mt-2">{{ $avis->titre }}</h3>
                            <p class="text-sm text-gray-700 mt-1">{{ $avis->contenu }}</p>
                        </div>
                    @endforeach

                    {{-- Formulaire avis --}}
                    <div class="bg-white border rounded-lg p-6 mt-6" x-data="{ submitError: '' }">
                        <h3 class="text-lg font-semibold mb-4">Donner votre avis</h3>
                        <form action="{{ route('avis.store') }}" method="POST" @submit="
                            const notes = ['note_ponctualite','note_qualite','note_prix','note_proprete','note_conseil'];
                            const missing = notes.filter(n => !$el.querySelector('[name='+n+']').value || $el.querySelector('[name='+n+']').value === '0');
                            if (missing.length) { submitError = 'Veuillez attribuer toutes les notes.'; $event.preventDefault(); return; }
                            submitError = '';
                        ">
                            @csrf
                            <input type="hidden" name="plombier_id" value="{{ $plombier->id }}">

                            @guest
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Votre pseudo <span class="text-red-500">*</span></label>
                                        <input type="text" name="pseudo_auteur" required class="w-full border rounded-lg px-3 py-2" value="{{ old('pseudo_auteur') }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Votre email <span class="text-red-500">*</span></label>
                                        <input type="email" name="email_auteur" required class="w-full border rounded-lg px-3 py-2" value="{{ old('email_auteur') }}">
                                    </div>
                                </div>
                            @endguest

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Type d'intervention</label>
                                <select name="type_intervention" class="w-full border rounded-lg px-3 py-2">
                                    <option value="">--</option>
                                    <option value="Dépannage">Dépannage</option>
                                    <option value="Installation">Installation</option>
                                    <option value="Entretien">Entretien</option>
                                    <option value="Devis">Devis</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Titre <span class="text-red-500">*</span></label>
                                <input type="text" name="titre" required class="w-full border rounded-lg px-3 py-2" value="{{ old('titre') }}">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Votre avis <span class="text-red-500">*</span></label>
                                <textarea name="contenu" rows="4" required class="w-full border rounded-lg px-3 py-2">{{ old('contenu') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
                                @foreach(['ponctualite' => 'Ponctualité', 'qualite' => 'Qualité', 'prix' => 'Prix', 'proprete' => 'Propreté', 'conseil' => 'Conseil'] as $key => $label)
                                    <x-star-rating-input name="note_{{ $key }}" :label="$label" :value="old('note_' . $key, 0)" />
                                @endforeach
                            </div>

                            <p x-show="submitError" x-text="submitError" class="text-red-500 text-sm mb-3" x-cloak></p>
                            <button type="submit" class="bg-blue-900 text-white px-6 py-2 rounded-lg hover:bg-blue-800">Envoyer mon avis</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div>
                @if($nearby->isNotEmpty())
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Plombiers à proximité</h3>
                        <div class="space-y-3">
                            @foreach($nearby as $proche)
                                <x-plombier-card :plombier="$proche" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Contact Modal --}}
    <div x-data="{ sent: false, sending: false, error: '' }" x-show="$store.contactModal.open" @keydown.escape.window="$store.contactModal.open = false" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="$store.contactModal.open = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 z-10" @click.stop>
            <button @click="$store.contactModal.open = false" type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <h2 class="text-xl font-bold mb-4">Contacter {{ $plombier->titre }}</h2>
            <div x-show="sent" class="text-center py-8">
                <p class="text-lg font-semibold text-green-600">Message envoyé !</p>
                <button @click="$store.contactModal.open = false" type="button" class="mt-4 bg-blue-900 text-white px-6 py-2 rounded-lg cursor-pointer">Fermer</button>
            </div>
            <form x-show="!sent" @submit.prevent="sending = true; error = ''; const fd = new FormData($el); fetch($el.action, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } }).then(r => { if (r.ok) { sent = true; } else { return r.json().then(d => { error = d.message || 'Erreur'; }); } }).catch(() => { error = 'Erreur réseau.'; }).finally(() => { sending = false; });" action="{{ route('demande.store') }}">
                @csrf
                <input type="hidden" name="plombier_id" value="{{ $plombier->id }}">
                <input type="hidden" name="type" value="depannage">
                <input type="hidden" name="urgence" value="normale">
                <div class="mb-3"><label class="block text-sm font-medium mb-1">Nom *</label><input type="text" name="nom" required class="w-full border rounded-lg px-3 py-2" value="{{ auth()->user()?->pseudo }}"></div>
                <div class="mb-3"><label class="block text-sm font-medium mb-1">Email *</label><input type="email" name="email" required class="w-full border rounded-lg px-3 py-2" value="{{ auth()->user()?->email }}"></div>
                <div class="mb-3"><label class="block text-sm font-medium mb-1">Téléphone *</label><input type="tel" name="telephone" required class="w-full border rounded-lg px-3 py-2"></div>
                <div class="mb-3"><label class="block text-sm font-medium mb-1">Code postal *</label><input type="text" name="cp" required maxlength="5" class="w-full border rounded-lg px-3 py-2"></div>
                <div class="mb-3"><label class="block text-sm font-medium mb-1">Description *</label><textarea name="description" rows="3" required class="w-full border rounded-lg px-3 py-2" placeholder="Décrivez votre problème..."></textarea></div>
                <p x-show="error" x-text="error" class="text-red-500 text-sm mb-3" x-cloak></p>
                <button type="submit" :disabled="sending" class="w-full bg-blue-900 text-white font-semibold py-3 rounded-lg hover:bg-blue-800 disabled:opacity-50 cursor-pointer">
                    <span x-show="!sending">Envoyer</span><span x-show="sending">Envoi...</span>
                </button>
            </form>
        </div>
    </div>

    @if($plombier->latitude && $plombier->longitude)
        <script src="https://unpkg.com/leaflet@1.9/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var map = L.map('map', { scrollWheelZoom: false }).setView([{{ $plombier->latitude }}, {{ $plombier->longitude }}], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                L.marker([{{ $plombier->latitude }}, {{ $plombier->longitude }}]).addTo(map).bindPopup('<strong>{{ e($plombier->titre) }}</strong>');
            });
        </script>
    @endif
</x-layouts.app>
