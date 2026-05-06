<x-layouts.app title="Contact - Plombier SOS" description="Contactez l'équipe Plombier SOS pour toute question, suggestion ou partenariat.">
    <div class="max-w-3xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Nous contacter</h1>
        <p class="text-gray-600 mb-8">Une question, une suggestion ou une demande de partenariat ? Envoyez-nous un message via le formulaire ci-dessous.</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="bg-white border rounded-lg p-6" x-data="{ sending: false, sent: false, error: '' }">
            <div x-show="sent" x-cloak class="text-center py-8">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-lg font-semibold text-gray-900">Message envoyé !</p>
                <p class="text-gray-500 mt-1">Nous vous répondrons dans les meilleurs délais.</p>
            </div>
            <form x-show="!sent" action="{{ route('contact.store') }}" method="POST" @submit.prevent="
                sending = true; error = '';
                const fd = new FormData($el);
                fetch($el.action, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
                    .then(r => { if (r.ok) { sent = true; } else { return r.json().then(d => { error = d.message || 'Erreur'; }); } })
                    .catch(() => { error = 'Erreur réseau.'; })
                    .finally(() => { sending = false; });
            ">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom *</label>
                        <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email *</label>
                        <input type="email" name="email" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Sujet *</label>
                    <select name="subject" required class="w-full border rounded-lg px-3 py-2">
                        <option value="">-- Choisir --</option>
                        <option value="question">Question générale</option>
                        <option value="suggestion">Suggestion</option>
                        <option value="partenariat">Partenariat</option>
                        <option value="reclamation">Réclamation de fiche</option>
                        <option value="bug">Signaler un problème</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Message *</label>
                    <textarea name="message" rows="5" required class="w-full border rounded-lg px-3 py-2" placeholder="Votre message..."></textarea>
                </div>
                <p x-show="error" x-text="error" class="text-red-500 text-sm mb-3" x-cloak></p>
                <button type="submit" :disabled="sending" class="bg-blue-900 text-white font-semibold px-6 py-3 rounded-lg hover:bg-blue-800 disabled:opacity-50 cursor-pointer">
                    <span x-show="!sending">Envoyer</span>
                    <span x-show="sending">Envoi...</span>
                </button>
            </form>
        </div>

        <div class="mt-8 text-sm text-gray-500">
            <p>Vous pouvez aussi nous écrire à <a href="mailto:contact@plombier-sos.fr" class="text-blue-600 hover:underline">contact@plombier-sos.fr</a></p>
        </div>
    </div>
</x-layouts.app>
