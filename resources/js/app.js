import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);

        if (!input) {
            return;
        }

        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        button.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
        button.setAttribute('aria-pressed', String(isHidden));
    });
});

document.querySelectorAll('[data-current-year]').forEach((element) => {
    element.textContent = new Intl.DateTimeFormat('id-ID', { year: 'numeric' }).format(new Date());
});

document.querySelectorAll('.table-admin').forEach((table) => {
    const labels = [...table.querySelectorAll('thead th')].map((heading) => heading.textContent.trim());

    table.querySelectorAll('tbody tr').forEach((row) => {
        [...row.children].forEach((cell, index) => {
            cell.dataset.label = labels[index] ?? '';
        });
    });
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (! window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-image-preview]').forEach((input) => {
    input.addEventListener('change', () => {
        const target = document.getElementById(input.dataset.imagePreview);
        const file = input.files?.[0];

        if (! target || ! file || ! file.type.startsWith('image/')) {
            return;
        }

        const reader = new FileReader();
        reader.addEventListener('load', () => {
            target.style.backgroundImage = `url(${reader.result})`;
            target.classList.add('has-preview');
        });
        reader.readAsDataURL(file);
    });
});

document.querySelectorAll('form[data-post-editor]').forEach((form) => {
    const editor = form.querySelector('[data-editor-surface]');
    const output = form.querySelector('[data-editor-output]');
    const imageList = form.querySelector('[data-inline-image-list]');
    const imageTemplate = form.querySelector('[data-inline-image-template]')
        ?? document.querySelector('[data-inline-image-template]');
    const imageCounter = form.querySelector('[data-image-counter]');
    const imageEmpty = form.querySelector('[data-inline-empty]');
    const addImageButton = form.querySelector('[data-add-inline-image]');
    const excerpt = form.querySelector('#post_excerpt');
    const excerptCounter = form.querySelector('[data-excerpt-count]');
    let savedRange = null;
    let reconciling = false;

    if (! editor || ! output || ! imageList || ! imageTemplate) {
        return;
    }

    const markerSelector = 'figure[data-image-id], figure[data-image-token]';

    const canonicalHtml = () => {
        const clone = editor.cloneNode(true);

        clone.querySelectorAll(markerSelector).forEach((figure) => {
            const canonical = document.createElement('figure');
            const imageId = figure.dataset.imageId;
            const imageToken = figure.dataset.imageToken;

            if (imageId) {
                canonical.dataset.imageId = imageId;
            } else if (imageToken) {
                canonical.dataset.imageToken = imageToken;
            }

            figure.replaceWith(canonical);
        });

        return clone.innerHTML.trim();
    };

    const syncOutput = () => {
        output.value = canonicalHtml();
    };

    const markerForToken = (token) => editor.querySelector(`figure[data-image-token="${token}"]`);
    const markerForId = (id) => editor.querySelector(`figure[data-image-id="${id}"]`);

    const removeNewImage = (token) => {
        markerForToken(token)?.remove();
        imageList.querySelector(`[data-new-image-item="${token}"]`)?.remove();
        reconcileImages();
    };

    const removeExistingImage = (id) => {
        markerForId(id)?.remove();
        imageList.querySelector(`[data-existing-image-item="${id}"]`)?.remove();
        reconcileImages();
    };

    const decorateMarker = (figure) => {
        figure.classList.add('editor-inline-figure');
        figure.setAttribute('contenteditable', 'false');

        if (figure.querySelector('[data-remove-editor-image]')) {
            return;
        }

        if (figure.dataset.imageToken && ! figure.querySelector('img')) {
            const placeholder = document.createElement('span');
            placeholder.className = 'editor-inline-placeholder';
            placeholder.textContent = 'Gambar baru — pilih file dan lengkapi alt text di panel media.';
            figure.append(placeholder);
        }

        const remove = document.createElement('button');
        remove.className = 'btn btn-sm btn-danger editor-inline-remove';
        remove.type = 'button';
        remove.dataset.removeEditorImage = '';
        remove.textContent = 'Hapus gambar';
        remove.addEventListener('click', () => {
            if (figure.dataset.imageToken) {
                removeNewImage(figure.dataset.imageToken);
            } else if (figure.dataset.imageId) {
                removeExistingImage(figure.dataset.imageId);
            }
        });
        figure.append(remove);
    };

    function reconcileImages() {
        if (reconciling) {
            return;
        }

        reconciling = true;

        editor.querySelectorAll(markerSelector).forEach(decorateMarker);
        imageList.querySelectorAll('[data-new-image-item]').forEach((item) => {
            if (! markerForToken(item.dataset.newImageItem)) {
                item.remove();
            }
        });
        imageList.querySelectorAll('[data-existing-image-item]').forEach((item) => {
            if (! markerForId(item.dataset.existingImageItem)) {
                item.remove();
            }
        });

        const imageCount = editor.querySelectorAll(markerSelector).length;
        imageCounter.textContent = `${imageCount}/10 gambar inline`;
        imageEmpty.hidden = imageCount > 0;
        addImageButton.disabled = imageCount >= 10;
        syncOutput();
        reconciling = false;
    }

    const rememberSelection = () => {
        const selection = window.getSelection();

        if (! selection || selection.rangeCount === 0) {
            return;
        }

        const range = selection.getRangeAt(0);
        const container = range.commonAncestorContainer.nodeType === Node.TEXT_NODE
            ? range.commonAncestorContainer.parentElement
            : range.commonAncestorContainer;

        if (container && editor.contains(container)) {
            savedRange = range.cloneRange();
        }
    };

    const restoreSelection = () => {
        if (! savedRange) {
            editor.focus();

            return;
        }

        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(savedRange);
    };

    const insertMarker = (token) => {
        const figure = document.createElement('figure');
        figure.dataset.imageToken = token;
        decorateMarker(figure);
        restoreSelection();

        const selection = window.getSelection();

        if (selection?.rangeCount) {
            const range = selection.getRangeAt(0);
            range.deleteContents();
            range.insertNode(figure);
            const paragraph = document.createElement('p');
            paragraph.append(document.createElement('br'));
            figure.after(paragraph);
            range.selectNodeContents(paragraph);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
            savedRange = range.cloneRange();
        } else {
            editor.append(figure);
        }
    };

    const makeToken = () => {
        if (window.crypto?.randomUUID) {
            return `img_${window.crypto.randomUUID().replaceAll('-', '')}`;
        }

        return `img_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`;
    };

    addImageButton.addEventListener('click', () => {
        if (editor.querySelectorAll(markerSelector).length >= 10) {
            window.alert('Satu artikel hanya boleh memiliki maksimal 10 gambar inline.');

            return;
        }

        const token = makeToken();
        insertMarker(token);
        imageList.insertAdjacentHTML('beforeend', imageTemplate.innerHTML.replaceAll('__TOKEN__', token));
        reconcileImages();
        imageList.querySelector(`[data-new-image-item="${token}"] input[type="file"]`)?.focus();
    });

    imageList.addEventListener('click', (event) => {
        const newButton = event.target.closest('[data-remove-inline-image]');
        const existingButton = event.target.closest('[data-remove-existing-image]');

        if (newButton) {
            removeNewImage(newButton.dataset.removeInlineImage);
        } else if (existingButton) {
            removeExistingImage(existingButton.dataset.removeExistingImage);
        }
    });

    imageList.addEventListener('change', (event) => {
        const input = event.target.closest('[data-inline-file]');
        const preview = input?.closest('[data-new-image-item]')?.querySelector('[data-inline-preview]');
        const file = input?.files?.[0];

        if (! input || ! preview) {
            return;
        }

        preview.replaceChildren();

        if (! file || ! file.type.startsWith('image/')) {
            return;
        }

        const image = document.createElement('img');
        const objectUrl = URL.createObjectURL(file);
        image.className = 'editor-media-thumb mt-2';
        image.alt = 'Preview gambar inline baru';
        image.src = objectUrl;
        image.addEventListener('load', () => URL.revokeObjectURL(objectUrl), { once: true });
        preview.append(image);
    });

    form.querySelectorAll('[data-editor-command]').forEach((button) => {
        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => {
            restoreSelection();
            const command = button.dataset.editorCommand;
            let value = button.dataset.editorValue ?? null;

            if (command === 'createLink') {
                value = window.prompt('Masukkan tautan HTTP(S), /path, #bagian, atau mailto:');

                if (! value) {
                    return;
                }

                const safeValue = value.trim();
                const isSafe = /^(https?:\/\/|\/|#|mailto:)/i.test(safeValue) && ! safeValue.startsWith('//');

                if (! isSafe) {
                    window.alert('Gunakan tautan HTTP(S), path internal, anchor, atau mailto yang valid.');

                    return;
                }

                value = safeValue;
            }

            document.execCommand(command, false, value);
            rememberSelection();
            syncOutput();
        });
    });

    ['focus', 'keyup', 'mouseup', 'input'].forEach((eventName) => {
        editor.addEventListener(eventName, () => {
            rememberSelection();
            syncOutput();
        });
    });

    excerpt?.addEventListener('input', () => {
        excerptCounter.textContent = String(excerpt.value.length);
    });

    const observer = new MutationObserver(() => reconcileImages());
    observer.observe(editor, { childList: true, subtree: true });
    form.addEventListener('submit', syncOutput);
    excerpt?.dispatchEvent(new Event('input'));
    reconcileImages();
});
