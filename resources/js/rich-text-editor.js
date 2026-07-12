import Quill from 'quill';
import QuillTableBetter from 'quill-table-better';
import 'quill/dist/quill.snow.css';
import 'quill-table-better/dist/quill-table-better.css';

Quill.register({'modules/table-better': QuillTableBetter}, true);

window.Quill = Quill;

const emptyEditorHtml = new Set(['', '<p><br></p>', '<p></p>']);

function normalizeEditorHtml(html) {
    if (! html || emptyEditorHtml.has(html)) {
        return '';
    }

    return html;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('richTextEditor', (wireModel, modelName) => ({
        quill: null,

        init() {
            this.quill = new Quill(this.$refs.editor, {
                theme: 'snow',
                placeholder: this.$refs.editor.dataset.placeholder || '',
                modules: {
                    toolbar: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote'],
                        ['link'],
                        ['table-better'],
                        ['clean'],
                    ],
                    table: false,
                    'table-better': {
                        language: 'en_US',
                        menus: ['column', 'row', 'merge', 'table', 'cell', 'wrap', 'delete'],
                        toolbarTable: true,
                    },
                    keyboard: {
                        bindings: QuillTableBetter.keyboardBindings,
                    },
                },
            });

            this.setQuillContent(wireModel);

            this.quill.on('text-change', () => {
                const next = normalizeEditorHtml(this.quill.root.innerHTML);

                if (wireModel !== next) {
                    wireModel = next;
                }
            });

            this.$watch(() => wireModel, (value) => {
                this.setQuillContent(value);
            });

            if (this.$wire && modelName) {
                this.$wire.$watch(modelName, (value) => {
                    this.setQuillContent(value);
                });
            }

            this.$el.addEventListener('rich-text-set', (event) => {
                this.setQuillContent(event.detail?.value);
            });
        },

        setQuillContent(value) {
            if (! this.quill) {
                return;
            }

            const next = normalizeEditorHtml(value);
            const current = normalizeEditorHtml(this.quill.root.innerHTML);

            if (current !== next) {
                this.quill.root.innerHTML = next;
            }
        },
    }));
});

document.addEventListener('livewire:init', () => {
    Livewire.on('rich-text-sync', ({ drafts }) => {
        if (! drafts || typeof drafts !== 'object') {
            return;
        }

        Object.entries(drafts).forEach(([model, value]) => {
            document.querySelectorAll(`[data-rich-text-model="${model}"]`).forEach((host) => {
                host.dispatchEvent(new CustomEvent('rich-text-set', {
                    detail: { value },
                }));
            });
        });
    });
});
