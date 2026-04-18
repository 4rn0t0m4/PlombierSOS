import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('contactModal', { open: false });

Alpine.data('phoneReveal', (encoded, plombierId) => ({
    revealed: false,
    loading: false,
    display: '',
    tel: '',
    code: null,
    premium: false,
    tarif: '',
    mobile: false,

    async reveal() {
        this.loading = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch('/ajax/phone', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    phone: encoded,
                    plumber_id: plombierId,
                }),
            });
            const data = await res.json();
            this.display = data.phone;
            this.tel = data.tel;
            this.code = data.code || null;
            this.premium = data.premium;
            this.tarif = data.tarif || '';
            this.mobile = data.mobile;
            this.revealed = true;
            this.$nextTick(() => {
                window.location.href = 'tel:' + this.tel;
            });
        } catch (e) {
            this.display = 'Erreur';
        }
        this.loading = false;
    },
}));

Alpine.data('villeAutocomplete', () => ({
    query: '',
    results: [],
    open: false,
    debounceTimer: null,

    search() {
        clearTimeout(this.debounceTimer);
        if (this.query.length < 2) {
            this.results = [];
            this.open = false;
            return;
        }
        this.debounceTimer = setTimeout(async () => {
            const res = await fetch('/ajax/villes?q=' + encodeURIComponent(this.query));
            this.results = await res.json();
            this.open = this.results.length > 0;
        }, 200);
    },

    select(item) {
        this.query = item.value;
        this.open = false;
        this.results = [];
    },
}));

Alpine.data('chatbot', () => ({
    open: false,
    messages: [],
    input: '',
    loading: false,
    city: '',
    postalCode: '',
    started: false,

    toggle() {
        this.open = !this.open;
        if (this.open && !this.started) {
            this.started = true;
            this.messages.push({
                role: 'assistant',
                content: 'Bonjour ! Je suis l\'assistant Plombier SOS. Décrivez-moi votre problème de plomberie et je vous aiderai à trouver une solution.',
            });
        }
    },

    async send() {
        const text = this.input.trim();
        if (!text || this.loading) return;

        this.messages.push({ role: 'user', content: text });
        this.input = '';
        this.loading = true;

        // Extract city/postal code from conversation
        const cpMatch = text.match(/\b(\d{5})\b/);
        if (cpMatch) this.postalCode = cpMatch[1];

        this.$nextTick(() => {
            this.scrollToBottom();
            this.$refs.chatInput?.focus();
        });

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const apiMessages = this.messages.filter(m => m.role !== 'system');

            const res = await fetch('/ajax/chatbot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    messages: apiMessages,
                    city: this.city,
                    postal_code: this.postalCode,
                }),
            });

            const data = await res.json();

            if (data.message) {
                this.messages.push({ role: 'assistant', content: data.message });
                if (data.city) this.city = data.city;
                if (data.postal_code) this.postalCode = data.postal_code;
            } else {
                this.messages.push({ role: 'assistant', content: data.error || 'Désolé, une erreur est survenue.' });
            }
        } catch (e) {
            this.messages.push({ role: 'assistant', content: 'Désolé, le service est temporairement indisponible.' });
        }

        this.loading = false;
        this.$nextTick(() => {
            this.scrollToBottom();
            this.$refs.chatInput?.focus();
        });
    },

    scrollToBottom() {
        const container = this.$refs.messages;
        if (container) container.scrollTop = container.scrollHeight;
    },

    formatMessage(content) {
        let html = content;
        // Convert markdown links [text](url) to HTML
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-600 underline hover:text-blue-800">$1</a>');
        // Convert bold **text**
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        // Convert line breaks
        html = html.replace(/\n/g, '<br>');
        return html;
    },
}));

Alpine.start();
