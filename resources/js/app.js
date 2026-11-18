import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('quizAttempt', (config) => ({
    remaining: Number(config.remaining),
    saving: false,
    message: '',
    savedAt: null,

    init() {
        this.timer = setInterval(() => {
            this.remaining = Math.max(0, this.remaining - 1);

            if (this.remaining === 0) {
                clearInterval(this.timer);
                this.disableForm();
                this.message = 'Waktu pengerjaan habis. Jawaban tidak dapat diubah lagi.';
            }
        }, 1000);
    },

    destroy() {
        clearInterval(this.timer);
    },

    clock() {
        const minutes = Math.floor(this.remaining / 60);
        const seconds = this.remaining % 60;

        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    },

    disableForm() {
        this.$refs.form?.querySelectorAll('input, textarea').forEach((element) => {
            element.disabled = true;
        });
    },

    async save(questionId, payload = {}) {
        if (this.remaining <= 0) {
            return;
        }

        const container = document.getElementById(`question-${questionId}`);

        if (container) {
            const checked = [...container.querySelectorAll('input[type="checkbox"]:checked')].map((input) => Number(input.value));
            const radio = container.querySelector('input[type="radio"]:checked');
            const text = container.querySelector('textarea, input[type="text"]');

            if (checked.length > 0) {
                payload.option_ids = checked;
            }

            if (radio) {
                payload.question_option_id = Number(radio.value);
            }

            if (text) {
                payload.answer_text = text.value;
            }
        }

        payload.question_id = questionId;

        this.saving = true;

        try {
            const response = await fetch(config.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': config.csrf,
                },
                body: JSON.stringify(payload),
            });

            if (! response.ok) {
                const body = await response.json().catch(() => ({}));
                this.message = body.message ?? 'Jawaban gagal disimpan. Periksa koneksi Anda.';

                return;
            }

            const body = await response.json();
            this.savedAt = new Date().toLocaleTimeString('id-ID');
            this.remaining = Number(body.remaining_seconds ?? this.remaining);
            this.message = '';
        } catch (error) {
            this.message = 'Jawaban gagal disimpan. Periksa koneksi Anda.';
        } finally {
            this.saving = false;
        }
    },
}));

Alpine.start();
