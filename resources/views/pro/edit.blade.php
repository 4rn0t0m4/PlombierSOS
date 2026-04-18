<x-layouts.app title="Modifier {{ $plumber->title }} - Espace Pro">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('pro.dashboard') }}" class="hover:text-blue-600">Espace Pro</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $plumber->title }}</span>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900 mb-8">Modifier votre fiche</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <form action="{{ route('pro.update', $plumber) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- Coordonnées --}}
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Coordonnées</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Téléphone fixe</label>
                        <input type="tel" name="phone" value="{{ old('phone', $plumber->phone) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Téléphone mobile</label>
                        <input type="tel" name="mobile_phone" value="{{ old('mobile_phone', $plumber->mobile_phone) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email professionnel</label>
                        <input type="email" name="email" value="{{ old('email', $plumber->email) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Site web</label>
                        <input type="url" name="website" value="{{ old('website', $plumber->website) }}" placeholder="https://..." class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium mb-1">Adresse</label>
                        <input type="text" name="address" value="{{ old('address', $plumber->address) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Description</h2>
                <textarea name="description" rows="5" class="w-full border rounded-lg px-3 py-2" placeholder="Présentez votre activité, vos services, votre expérience...">{{ old('description', $plumber->description) }}</textarea>
            </div>

            {{-- Services --}}
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Services</h2>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="emergency_24h" value="0">
                        <input type="checkbox" name="emergency_24h" value="1" {{ old('emergency_24h', $plumber->emergency_24h) ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-blue-600">
                        <span>Disponible en urgence 24h/24</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="free_quote" value="0">
                        <input type="checkbox" name="free_quote" value="1" {{ old('free_quote', $plumber->free_quote) ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-blue-600">
                        <span>Devis gratuit</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="rge_certified" value="0">
                        <input type="checkbox" name="rge_certified" value="1" {{ old('rge_certified', $plumber->rge_certified) ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-blue-600">
                        <span>Certifié RGE</span>
                    </label>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Rayon d'intervention (km)</label>
                    <input type="number" name="service_radius" value="{{ old('service_radius', $plumber->service_radius) }}" min="1" max="100" class="w-32 border rounded-lg px-3 py-2">
                </div>
            </div>

            {{-- Spécialités --}}
            <div class="bg-white border rounded-lg p-6" x-data="{
                specs: {{ json_encode(old('specialties', $plumber->specialties ?? [])) }},
                newSpec: '',
                add() {
                    if (this.newSpec.trim() && !this.specs.includes(this.newSpec.trim())) {
                        this.specs.push(this.newSpec.trim());
                        this.newSpec = '';
                    }
                },
                remove(i) { this.specs.splice(i, 1); }
            }">
                <h2 class="text-xl font-semibold mb-4">Spécialités</h2>
                <div class="flex flex-wrap gap-2 mb-3">
                    <template x-for="(spec, i) in specs" :key="i">
                        <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full flex items-center gap-1">
                            <span x-text="spec"></span>
                            <input type="hidden" name="specialties[]" :value="spec">
                            <button type="button" @click="remove(i)" class="text-blue-600 hover:text-blue-800 cursor-pointer">&times;</button>
                        </span>
                    </template>
                </div>
                <div class="flex gap-2">
                    <input type="text" x-model="newSpec" @keydown.enter.prevent="add()" placeholder="Ex: Fuite d'eau, Chaudière, Débouchage..." class="flex-1 border rounded-lg px-3 py-2 text-sm">
                    <button type="button" @click="add()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-300 cursor-pointer">Ajouter</button>
                </div>
            </div>

            {{-- Horaires --}}
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Horaires d'ouverture</h2>
                @php
                    $days = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
                    $existingSchedules = $plumber->schedules->keyBy('day_of_week');
                @endphp
                <div class="space-y-3">
                    @foreach($days as $dayNum => $dayName)
                        @php $schedule = $existingSchedules[$dayNum] ?? null; @endphp
                        <div class="flex items-center gap-3 flex-wrap" x-data="{ closed: {{ ($schedule?->is_closed ?? false) ? 'true' : 'false' }} }">
                            <span class="w-24 text-sm font-medium">{{ $dayName }}</span>
                            <label class="flex items-center gap-1 text-sm cursor-pointer">
                                <input type="checkbox" name="schedules[{{ $dayNum }}][is_closed]" value="1" x-model="closed" {{ ($schedule?->is_closed ?? false) ? 'checked' : '' }} class="rounded border-gray-300">
                                Fermé
                            </label>
                            <div x-show="!closed" class="flex items-center gap-1 text-sm">
                                <input type="time" name="schedules[{{ $dayNum }}][morning_open]" value="{{ $schedule?->morning_open ? substr($schedule->morning_open, 0, 5) : '08:00' }}" class="border rounded px-2 py-1 text-sm">
                                <span>-</span>
                                <input type="time" name="schedules[{{ $dayNum }}][morning_close]" value="{{ $schedule?->morning_close ? substr($schedule->morning_close, 0, 5) : '12:00' }}" class="border rounded px-2 py-1 text-sm">
                                <span class="mx-1">/</span>
                                <input type="time" name="schedules[{{ $dayNum }}][afternoon_open]" value="{{ $schedule?->afternoon_open ? substr($schedule->afternoon_open, 0, 5) : '14:00' }}" class="border rounded px-2 py-1 text-sm">
                                <span>-</span>
                                <input type="time" name="schedules[{{ $dayNum }}][afternoon_close]" value="{{ $schedule?->afternoon_close ? substr($schedule->afternoon_close, 0, 5) : '18:00' }}" class="border rounded px-2 py-1 text-sm">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-900 text-white font-semibold px-8 py-3 rounded-lg hover:bg-blue-800 cursor-pointer">Enregistrer les modifications</button>
                <a href="{{ route('pro.dashboard') }}" class="bg-gray-200 text-gray-700 font-semibold px-8 py-3 rounded-lg hover:bg-gray-300">Retour</a>
            </div>
        </form>
    </div>
</x-layouts.app>
