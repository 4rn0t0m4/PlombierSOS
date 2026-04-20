<x-layouts.app
    :title="$plumber->title . ' - ' . $plumber->type_label . ' à ' . $plumber->city . ' - Plombier SOS'"
    :description="$plumber->title . ', ' . strtolower($plumber->type_label) . ' à ' . $plumber->city . ($plumber->google_rating ? '. Note Google : ' . $plumber->google_rating . '/5' : '') . '. Téléphone, horaires, avis.'"
>
    @if($plumber->latitude && $plumber->longitude)
        @push('head')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css" />
        @endpush
    @endif

    @push('jsonld')
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Plumber',
            'name' => $plumber->title,
            'url' => url($plumber->url),
            'telephone' => $plumber->phone,
        ];
        if ($plumber->address) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $plumber->address,
                'addressLocality' => $plumber->city,
                'postalCode' => $plumber->postal_code,
                'addressCountry' => 'FR',
            ];
        }
        if ($plumber->latitude && $plumber->longitude) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $plumber->latitude,
                'longitude' => $plumber->longitude,
            ];
        }
        if ($plumber->google_rating) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $plumber->google_rating,
                'reviewCount' => $plumber->google_reviews_count,
                'bestRating' => 5,
            ];
        }
        if ($plumber->website) {
            $schema['sameAs'] = $plumber->website;
        }
        if ($plumber->emergency_24h) {
            $schema['openingHours'] = 'Mo-Su 00:00-23:59';
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush

    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Accueil</a>
            <span class="mx-1">/</span>
            <a href="{{ route('departement.show', $department->slug) }}" class="hover:text-blue-600">{{ $department->name }}</a>
            <span class="mx-1">/</span>
            <a href="{{ route('ville.show', [$department->slug, $city->slug]) }}" class="hover:text-blue-600">{{ $city->name }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $plumber->title }}</span>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <h1 class="text-3xl font-bold text-gray-900">{{ $plumber->title }}</h1>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-blue-600">{{ $plumber->type_label }}</span>
                    <x-statut-ouverture :plombier="$plumber" />
                </div>

                @if($plumber->google_rating)
                    <div class="flex items-center gap-2 mt-3">
                        <x-star-rating :rating="$plumber->google_rating" />
                        <span class="font-semibold">{{ number_format($plumber->google_rating, 1, ',', '') }}/5</span>
                        <span class="text-gray-500">({{ $plumber->google_reviews_count }} avis Google)</span>
                    </div>
                @endif

                @if($plumber->city_ranking > 0 && $totalInCity > 1)
                    <p class="text-sm text-gray-600 mt-2">
                        Classé <span class="font-semibold text-blue-600">n°{{ $plumber->city_ranking }}</span>
                        sur {{ $totalInCity }} plombiers à {{ $plumber->city }}
                    </p>
                @endif

                <div class="flex gap-2 mt-3">
                    @if($plumber->emergency_24h)
                        <span class="text-sm bg-red-100 text-red-700 px-2 py-1 rounded">Urgence 24h/24</span>
                    @endif
                    @if($plumber->free_quote)
                        <span class="text-sm bg-green-100 text-green-700 px-2 py-1 rounded">Devis gratuit</span>
                    @endif
                    @if($plumber->rge_certified)
                        <span class="text-sm bg-blue-100 text-blue-700 px-2 py-1 rounded">Certifié RGE</span>
                    @endif
                </div>

                {{-- Coordonnées --}}
                <div class="mt-8 bg-white border rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Coordonnées</h2>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            @if($plumber->address)
                                <div>
                                    <p class="text-sm text-gray-500">Adresse</p>
                                    <p class="text-sm font-medium">{{ $plumber->address }}<br>{{ $plumber->postal_code }} {{ $plumber->city }}</p>
                                </div>
                            @endif
                            @if($plumber->phone)
                                <x-phone-reveal :phone="$plumber->phone" :plombier-id="$plumber->id" />
                            @endif
                            <div class="pt-2">
                                <button @click="$store.contactModal.open = true" type="button" class="w-full flex items-center justify-center gap-2 bg-blue-900 text-white font-semibold py-3 px-5 rounded-lg hover:bg-blue-800 transition cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                    Contacter
                                </button>
                            </div>
                            @if($plumber->website)
                                <a href="{{ $plumber->website }}" target="_blank" class="block text-sm text-blue-600 hover:underline break-all">{{ $plumber->website }}</a>
                            @endif
                        </div>
                        @if($plumber->latitude && $plumber->longitude)
                            <div class="rounded-lg overflow-hidden border" id="map" style="min-height: 220px;"></div>
                        @endif
                    </div>
                </div>

                {{-- Réclamation de fiche --}}
                <div class="mt-6" x-data="{ showClaim: false, claimSent: false, claimSending: false, claimError: '' }">
                    <button @click="showClaim = !showClaim" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        Vous êtes le propriétaire de cet établissement ?
                    </button>
                    <div x-show="showClaim" x-cloak class="mt-3 bg-gray-50 border rounded-lg p-5">
                        <div x-show="claimSent" class="text-center py-4">
                            <p class="text-green-600 font-semibold">Demande envoyée !</p>
                            <p class="text-sm text-gray-500 mt-1">Nous reviendrons vers vous rapidement.</p>
                        </div>
                        <form x-show="!claimSent" @submit.prevent="
                            claimSending = true; claimError = '';
                            const fd = new FormData($el);
                            fetch('{{ route('claim.store') }}', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
                                .then(r => { if (r.ok) { claimSent = true; } else { return r.json().then(d => { claimError = d.message || 'Erreur'; }); } })
                                .catch(() => { claimError = 'Erreur réseau.'; })
                                .finally(() => { claimSending = false; });
                        ">
                            @csrf
                            <input type="hidden" name="plumber_id" value="{{ $plumber->id }}">
                            <h3 class="font-semibold mb-3">Réclamer cette fiche</h3>
                            <p class="text-sm text-gray-600 mb-4">Remplissez ce formulaire pour prendre la main sur votre fiche et mettre à jour vos informations.</p>
                            <div class="grid sm:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Nom complet *</label>
                                    <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Email professionnel *</label>
                                    <input type="email" name="email" required class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Téléphone *</label>
                                    <input type="tel" name="phone" required class="w-full border rounded-lg px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Votre rôle *</label>
                                    <select name="role" required class="w-full border rounded-lg px-3 py-2 text-sm">
                                        <option value="owner">Gérant / Propriétaire</option>
                                        <option value="manager">Responsable</option>
                                        <option value="employee">Employé</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium mb-1">Message (optionnel)</label>
                                <textarea name="message" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Informations complémentaires, modifications souhaitées..."></textarea>
                            </div>
                            <p x-show="claimError" x-text="claimError" class="text-red-500 text-sm mb-3" x-cloak></p>
                            <button type="submit" :disabled="claimSending" class="bg-blue-900 text-white text-sm font-semibold px-5 py-2 rounded-lg hover:bg-blue-800 disabled:opacity-50 cursor-pointer">
                                <span x-show="!claimSending">Envoyer ma demande</span>
                                <span x-show="claimSending">Envoi...</span>
                            </button>
                        </form>
                    </div>
                </div>

                @if($plumber->seo_content || $plumber->description)
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Présentation</h2>
                        @if($plumber->seo_content)
                            <div class="prose text-gray-700">{!! app(\App\Services\SeoLinkService::class)->addLinks($plumber->seo_content) !!}</div>
                        @elseif($plumber->description)
                            <div class="prose text-gray-700">{!! nl2br(e($plumber->description)) !!}</div>
                        @endif
                    </div>
                @endif

                @if($plumber->schedules->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Horaires</h2>
                        <table class="w-full text-sm">
                            @foreach($plumber->schedules as $h)
                                <tr class="border-b">
                                    <td class="py-2 font-medium">{{ $h->day_label }}</td>
                                    <td class="py-2 text-gray-600">
                                        @if($h->is_closed) <span class="text-red-500">Fermé</span>
                                        @else
                                            {{ $h->morning_open ? substr($h->morning_open, 0, 5) . ' - ' . substr($h->morning_close, 0, 5) : '' }}
                                            @if($h->afternoon_open) / {{ substr($h->afternoon_open, 0, 5) }} - {{ substr($h->afternoon_close, 0, 5) }} @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif

                {{-- Avis Google --}}
                @if($plumber->google_reviews)
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                            Avis Google
                            <span class="text-sm font-normal text-gray-500">({{ $plumber->google_reviews_count }})</span>
                        </h2>
                        @if($plumber->reviews_summary)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/></svg>
                                    <span class="text-sm font-semibold text-blue-800">Résumé IA des avis</span>
                                </div>
                                <p class="text-sm text-blue-900">{{ $plumber->reviews_summary }}</p>
                            </div>
                        @endif
                        @foreach($plumber->google_reviews as $gReview)
                            <div class="bg-white border rounded-lg p-4 mb-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-2">
                                        @if($gReview['photo'] ?? null)
                                            <img src="{{ $gReview['photo'] }}" alt="" class="w-8 h-8 rounded-full">
                                        @endif
                                        <span class="font-semibold">{{ $gReview['author'] }}</span>
                                        @if($gReview['date'] ?? null)
                                            <span class="text-sm text-gray-400">{{ \Carbon\Carbon::parse($gReview['date'])->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <x-star-rating :rating="$gReview['rating']" size="w-4 h-4" />
                                        <span class="text-sm font-semibold">{{ $gReview['rating'] }}/5</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700 mt-2">{{ $gReview['text'] }}</p>
                            </div>
                        @endforeach
                        @if($plumber->google_maps_url)
                            <a href="{{ $plumber->google_maps_url }}" target="_blank" class="text-sm text-blue-600 hover:underline">Voir tous les avis sur Google →</a>
                        @endif
                    </div>
                @endif

                {{-- Avis du site --}}
                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-3">Avis clients ({{ $plumber->approvedReviews->count() }})</h2>

                    @foreach($plumber->approvedReviews as $review)
                        <div class="bg-white border rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-semibold">{{ $review->author_name }}</span>
                                    <span class="text-sm text-gray-400 ml-2">{{ $review->created_at->format('d/m/Y') }}</span>
                                    @if($review->intervention_type)
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded ml-2">{{ $review->intervention_type }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-star-rating :rating="$review->average_rating" size="w-4 h-4" />
                                    <span class="text-sm font-semibold">{{ number_format($review->average_rating, 1, ',', '') }}</span>
                                </div>
                            </div>
                            <h3 class="font-medium mt-2">{{ $review->title }}</h3>
                            <p class="text-sm text-gray-700 mt-1">{{ $review->content }}</p>
                        </div>
                    @endforeach

                    {{-- Formulaire avis --}}
                    <div class="bg-white border rounded-lg p-6 mt-6" x-data="{ submitError: '' }">
                        <h3 class="text-lg font-semibold mb-4">Donner votre avis</h3>
                        <form action="{{ route('avis.store') }}" method="POST" @submit="
                            const notes = ['punctuality_rating','quality_rating','price_rating','cleanliness_rating','advice_rating'];
                            const missing = notes.filter(n => !$el.querySelector('[name='+n+']').value || $el.querySelector('[name='+n+']').value === '0');
                            if (missing.length) { submitError = 'Veuillez attribuer toutes les notes.'; $event.preventDefault(); return; }
                            submitError = '';
                        ">
                            @csrf
                            <input type="hidden" name="plumber_id" value="{{ $plumber->id }}">

                            @guest
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Votre pseudo <span class="text-red-500">*</span></label>
                                        <input type="text" name="author_username" required class="w-full border rounded-lg px-3 py-2" value="{{ old('author_username') }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Votre email <span class="text-red-500">*</span></label>
                                        <input type="email" name="author_email" required class="w-full border rounded-lg px-3 py-2" value="{{ old('author_email') }}">
                                    </div>
                                </div>
                            @endguest

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Type d'intervention</label>
                                <select name="intervention_type" class="w-full border rounded-lg px-3 py-2">
                                    <option value="">--</option>
                                    <option value="Dépannage">Dépannage</option>
                                    <option value="Installation">Installation</option>
                                    <option value="Entretien">Entretien</option>
                                    <option value="Devis">Devis</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Titre <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required class="w-full border rounded-lg px-3 py-2" value="{{ old('title') }}">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Votre avis <span class="text-red-500">*</span></label>
                                <textarea name="content" rows="4" required class="w-full border rounded-lg px-3 py-2">{{ old('content') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
                                @foreach(['punctuality' => 'Ponctualité', 'quality' => 'Qualité', 'price' => 'Prix', 'cleanliness' => 'Propreté', 'advice' => 'Conseil'] as $key => $label)
                                    <x-star-rating-input name="{{ $key }}_rating" :label="$label" :value="old($key . '_rating', 0)" />
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
            <h2 class="text-xl font-bold mb-4">Contacter {{ $plumber->title }}</h2>
            <div x-show="sent" class="text-center py-8">
                <p class="text-lg font-semibold text-green-600">Message envoyé !</p>
                <button @click="$store.contactModal.open = false" type="button" class="mt-4 bg-blue-900 text-white px-6 py-2 rounded-lg cursor-pointer">Fermer</button>
            </div>
            <form x-show="!sent" @submit.prevent="sending = true; error = ''; const fd = new FormData($el); fetch($el.action, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } }).then(r => { if (r.ok) { sent = true; } else { return r.json().then(d => { error = d.message || 'Erreur'; }); } }).catch(() => { error = 'Erreur réseau.'; }).finally(() => { sending = false; });" action="{{ route('demande.store') }}">
                @csrf
                <input type="hidden" name="plumber_id" value="{{ $plumber->id }}">
                <input type="hidden" name="type" value="repair">
                <input type="hidden" name="urgency" value="normal">
                <div class="mb-3"><label class="block text-sm font-medium mb-1">Nom *</label><input type="text" name="name" required class="w-full border rounded-lg px-3 py-2" value="{{ auth()->user()?->username }}"></div>
                <div class="mb-3"><label class="block text-sm font-medium mb-1">Email *</label><input type="email" name="email" required class="w-full border rounded-lg px-3 py-2" value="{{ auth()->user()?->email }}"></div>
                <div class="mb-3"><label class="block text-sm font-medium mb-1">Téléphone *</label><input type="tel" name="phone" required class="w-full border rounded-lg px-3 py-2"></div>
                <div class="mb-3"><label class="block text-sm font-medium mb-1">Code postal *</label><input type="text" name="postal_code" required maxlength="5" class="w-full border rounded-lg px-3 py-2"></div>
                <div class="mb-3"><label class="block text-sm font-medium mb-1">Description *</label><textarea name="description" rows="3" required class="w-full border rounded-lg px-3 py-2" placeholder="Décrivez votre problème..."></textarea></div>
                <p x-show="error" x-text="error" class="text-red-500 text-sm mb-3" x-cloak></p>
                <button type="submit" :disabled="sending" class="w-full bg-blue-900 text-white font-semibold py-3 rounded-lg hover:bg-blue-800 disabled:opacity-50 cursor-pointer">
                    <span x-show="!sending">Envoyer</span><span x-show="sending">Envoi...</span>
                </button>
            </form>
        </div>
    </div>

    @if($plumber->latitude && $plumber->longitude)
        <script src="https://unpkg.com/leaflet@1.9/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var map = L.map('map', { scrollWheelZoom: false }).setView([{{ $plumber->latitude }}, {{ $plumber->longitude }}], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', { attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://www.openstreetmap.fr">OSM France</a>', maxZoom: 20 }).addTo(map);
                L.marker([{{ $plumber->latitude }}, {{ $plumber->longitude }}]).addTo(map).bindPopup('<strong>{{ e($plumber->title) }}</strong>');
            });
        </script>
    @endif
</x-layouts.app>
