/**
 * File upload zone (<x-ui.upload>): validates the picked files, sends each one with XMLHttpRequest to show
 * its progress, keeps the server id in a hidden input and removes files from the server when asked.
 *
 * Blade: the root carries x-data="upload", data-config (urls, accept, limits) and data-files (the files
 * already on the server); the <input type="file"> has x-ref="input" and x-on:change="onPick". The CSRF
 * token comes from <meta name="csrf-token">. Every access to the DOM, fetch and XMLHttpRequest lives here.
 *
 * A file only gets its hidden input once the server has answered, so the surrounding form waits while a
 * file is queued or uploading: its submit is cancelled (before the busy state of x-ui.form) and the zone
 * says why, without moving the focus.
 */
const UNITS = ['B', 'kB', 'MB', 'GB'];
const WAIT = 'Espera a que termine la subida';
const STATUS = {
    initial: 'Ya en el servidor',
    queued: 'En cola',
    uploading: 'Subiendo',
    done: 'Subido',
    error: 'No se ha podido subir',
};

function formatSize(bytes) {
    if (bytes === null || bytes === undefined || Number.isNaN(Number(bytes))) {
        return '';
    }

    let value = Number(bytes);
    let unit = 0;

    while (value >= 1024 && unit < UNITS.length - 1) {
        value /= 1024;
        unit += 1;
    }

    const formatted = new Intl.NumberFormat('es-ES', {
        maximumFractionDigits: unit === 0 ? 0 : 1,
    }).format(value);

    return `${formatted} ${UNITS[unit]}`;
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/** Does the file match an accept list such as "image/*,.pdf,application/zip"? */
function accepts(file, accept) {
    if (!accept) {
        return true;
    }

    const name = file.name.toLowerCase();
    const type = (file.type || '').toLowerCase();

    return accept
        .split(',')
        .map((token) => token.trim().toLowerCase())
        .filter(Boolean)
        .some((token) => {
            if (token.startsWith('.')) {
                return name.endsWith(token);
            }

            if (token.endsWith('/*')) {
                return type.startsWith(token.slice(0, -1));
            }

            return type === token;
        });
}

function describe(file) {
    file.statusLabel = file.status === 'error' ? (file.error ?? STATUS.error) : STATUS[file.status];
    file.progressLabel = `Subiendo ${file.name}, ${file.progress} %`;
}

export default function upload() {
    // The File objects and the requests stay out of the reactive state
    const raw = new Map();
    const requests = new Map();
    let counter = 0;
    let dragDepth = 0;
    let form = null;
    let onSubmit = null;

    return {
        files: [],
        dragging: false,
        config: {},
        hiddenName: '',
        problem: '',

        init() {
            this.config = JSON.parse(this.$root.dataset.config);
            this.hiddenName = this.config.name;

            const initial = JSON.parse(this.$root.dataset.files || '[]');

            this.files = initial.map((file) =>
                this.entry({
                    name: file.name,
                    size: file.size,
                    serverId: file.id,
                    status: 'initial',
                }),
            );

            form = this.$root.closest('form');

            if (form) {
                // Capture phase: on the form itself it runs before the x-on:submit of busyForm
                onSubmit = (event) => this.holdSubmit(event);
                form.addEventListener('submit', onSubmit, true);
            }
        },

        /** Files still on their way: queued or uploading. */
        get uploading() {
            return this.files.some(
                (file) => file.status === 'queued' || file.status === 'uploading',
            );
        },

        /** The form would leave without the files that have no hidden input yet, so it waits for them. */
        holdSubmit(event) {
            if (!this.uploading) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            this.problem = WAIT;
        },

        /** Once nothing is on its way, the request to wait has done its job. */
        settle() {
            if (!this.uploading && this.problem === WAIT) {
                this.problem = '';
            }
        },

        entry({ name, size, serverId = null, status }) {
            counter += 1;

            const file = {
                key: `file-${counter}`,
                name,
                size: size ?? null,
                sizeLabel: formatSize(size),
                progress: 0,
                status,
                serverId,
                error: null,
                statusLabel: '',
                progressLabel: '',
                removeLabel: `Quitar ${name}`,
                retryLabel: `Reintentar ${name}`,
            };

            describe(file);

            return file;
        },

        onPick() {
            const picked = Array.from(this.$refs.input.files);

            // Emptied here so the form never posts the files a second time
            this.$refs.input.value = '';
            this.add(picked);
        },

        onDragEnter() {
            dragDepth += 1;
            this.dragging = true;
        },

        onDragOver() {
            this.dragging = true;
        },

        onDragLeave() {
            dragDepth = Math.max(0, dragDepth - 1);

            if (dragDepth === 0) {
                this.dragging = false;
            }
        },

        onDrop(event) {
            dragDepth = 0;
            this.dragging = false;
            this.add(Array.from(event.dataTransfer?.files ?? []));
        },

        add(list) {
            const { multiple, maxFiles, maxSize, maxTotal, accept } = this.config;
            const problems = [];
            const queued = [];
            let total = this.files.reduce((sum, file) => sum + (file.size ?? 0), 0);
            let count = this.files.length;

            for (const file of list) {
                if (!multiple && count >= 1) {
                    problems.push('Solo se admite un archivo');
                    break;
                }

                if (maxFiles && count >= maxFiles) {
                    problems.push(`Solo se admiten ${maxFiles} archivos`);
                    break;
                }

                if (!accepts(file, accept)) {
                    problems.push(`${file.name}: tipo de archivo no admitido`);
                    continue;
                }

                if (maxSize && file.size > maxSize) {
                    problems.push(`${file.name}: el archivo pesa más de ${formatSize(maxSize)}`);
                    continue;
                }

                if (maxTotal && total + file.size > maxTotal) {
                    problems.push(
                        `${file.name}: se ha superado el tamaño total de ${formatSize(maxTotal)}`,
                    );
                    continue;
                }

                total += file.size;
                count += 1;

                const entry = this.entry({ name: file.name, size: file.size, status: 'queued' });

                raw.set(entry.key, file);
                this.files.push(entry);
                queued.push(entry.key);
            }

            this.problem = problems.join('. ');

            queued.forEach((key) => this.send(key));
        },

        send(key) {
            const file = this.find(key);
            const blob = raw.get(key);

            if (!file || !blob) {
                return;
            }

            file.status = 'uploading';
            file.progress = 0;
            file.error = null;
            describe(file);

            const xhr = new XMLHttpRequest();
            const data = new FormData();

            data.append('files[]', blob, blob.name);
            xhr.open('POST', this.config.url);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken());
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'text/plain');

            xhr.upload.onprogress = (event) => {
                if (event.lengthComputable) {
                    file.progress = Math.round((event.loaded / event.total) * 100);
                    describe(file);
                }
            };

            xhr.onload = () => {
                requests.delete(key);

                if (xhr.status >= 200 && xhr.status < 300) {
                    file.serverId = xhr.responseText.trim();
                    file.progress = 100;
                    file.status = 'done';
                } else {
                    this.fail(file);
                }

                describe(file);
                this.settle();
            };

            xhr.onerror = () => {
                requests.delete(key);
                this.fail(file);
                describe(file);
                this.settle();
            };

            xhr.onabort = () => requests.delete(key);

            requests.set(key, xhr);
            xhr.send(data);
        },

        /** The row says what happened and the alert of the zone, next to it, announces it once. */
        fail(file) {
            file.status = 'error';
            file.error = STATUS.error;
            this.problem = `${file.name}: no se ha podido subir`;
        },

        retry(key) {
            this.problem = '';
            this.send(key);
        },

        remove(key) {
            const file = this.find(key);

            if (!file) {
                return;
            }

            requests.get(key)?.abort();

            const headers = { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' };

            if (file.status === 'done' && file.serverId && this.config.revertUrl) {
                fetch(this.config.revertUrl, {
                    method: 'DELETE',
                    body: file.serverId,
                    headers: { ...headers, 'Content-Type': 'text/plain' },
                }).catch(() => {});
            } else if (file.status === 'initial' && this.config.removeUrl) {
                fetch(this.removeUrlFor(file.name), { headers }).catch(() => {});
            }

            raw.delete(key);
            this.files.splice(
                this.files.findIndex((item) => item.key === key),
                1,
            );
            this.problem = '';
            this.$refs.input.focus();
        },

        /** The remove url carries the placeholder NAME, replaced by the file name (last occurrence). */
        removeUrlFor(name) {
            const url = this.config.removeUrl;
            const at = url.lastIndexOf('NAME');

            if (at === -1) {
                return url;
            }

            return url.slice(0, at) + encodeURIComponent(name) + url.slice(at + 4);
        },

        find(key) {
            return this.files.find((file) => file.key === key) ?? null;
        },

        get summary() {
            const count = this.files.length;

            if (!count) {
                return 'Sin archivos';
            }

            const total = this.files.reduce((sum, file) => sum + (file.size ?? 0), 0);
            const uploading = this.files.filter((file) => file.status === 'uploading').length;
            const base = `${count} ${count === 1 ? 'archivo' : 'archivos'}${total ? `, ${formatSize(total)}` : ''}`;

            return uploading ? `${base}, subiendo ${uploading}` : base;
        },

        destroy() {
            requests.forEach((xhr) => xhr.abort());
            requests.clear();
            raw.clear();

            if (form && onSubmit) {
                form.removeEventListener('submit', onSubmit, true);
            }
        },
    };
}
