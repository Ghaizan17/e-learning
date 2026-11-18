import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('portalShell', () => ({
    sidebarOpen: false,
    confirmOpen: false,
    confirmMessage: '',
    confirmCallback: null,

    askConfirm(message, callback) {
        this.confirmMessage = message;
        this.confirmCallback = callback;
        this.confirmOpen = true;

        this.$nextTick(() => this.$refs.confirmButton?.focus());
    },

    cancelConfirm() {
        this.confirmOpen = false;
        this.confirmMessage = '';
        this.confirmCallback = null;
    },

    resolveConfirm() {
        const callback = this.confirmCallback;
        this.cancelConfirm();
        callback?.();
    },

    handleConfirmSubmit(event) {
        const form = event.target;

        if (! form.dataset?.confirm || form.dataset.confirmed === '1') {
            return;
        }

        event.preventDefault();

        this.askConfirm(form.dataset.confirm, () => {
            form.dataset.confirmed = '1';
            form.requestSubmit();
            delete form.dataset.confirmed;
        });
    },

    handleConfirmClick(event) {
        const trigger = event.target.closest?.('[data-confirm-form]');

        if (! trigger) {
            return;
        }

        event.preventDefault();

        const formId = trigger.dataset.confirmForm;

        this.askConfirm(trigger.dataset.confirm ?? 'Lanjutkan tindakan ini?', () => {
            document.getElementById(formId)?.requestSubmit();
        });
    },
}));

Alpine.data('richEditor', ({ initial = '', name = 'content_html' } = {}) => ({
    linkOpen: false,
    linkUrl: '',
    savedRange: null,
    linkId: `rich-link-${Math.random().toString(36).slice(2, 8)}`,

    init() {
        this.$refs.editor.innerHTML = initial;
        document.execCommand('defaultParagraphSeparator', false, 'p');
        this.sync();
    },

    sync() {
        this.$refs.input.value = this.$refs.editor.innerHTML;
    },

    format(command, value = null) {
        this.$refs.editor.focus();
        document.execCommand(command, false, value);
        this.sync();
    },

    block(tag) {
        this.format('formatBlock', tag);
    },

    openLink() {
        const selection = window.getSelection();

        if (selection?.rangeCount > 0 && this.$refs.editor.contains(selection.anchorNode)) {
            this.savedRange = selection.getRangeAt(0).cloneRange();
        } else {
            this.savedRange = null;
        }

        this.linkUrl = '';
        this.linkOpen = true;

        this.$nextTick(() => this.$refs.linkInput?.focus());
    },

    closeLink() {
        this.linkOpen = false;
        this.linkUrl = '';
    },

    applyLink() {
        const url = this.linkUrl.trim();

        if (url === '') {
            return;
        }

        this.$refs.editor.focus();

        if (this.savedRange) {
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(this.savedRange);
        }

        document.execCommand('createLink', false, url);
        this.sync();
        this.closeLink();
    },
}));

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
