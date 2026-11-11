/**
 * Topbar work stopwatch (<x-ui.work-timer>): starts, pauses, resumes and stops a titled work session on the server,
 * keeps ticking across page navigations and browser reloads, offers a direct «Crear evidencia» link on stop, and
 * lets the user select multiple recent sessions to sum their duration into a single new evidence.
 */
function formatClock(seconds) {
    const clamped = Math.max(0, Math.floor(Number(seconds) || 0));
    const hours = Math.floor(clamped / 3600);
    const minutes = Math.floor((clamped % 3600) / 60);
    const secs = clamped % 60;
    const pad = (n) => String(n).padStart(2, '0');

    return `${pad(hours)}:${pad(minutes)}:${pad(secs)}`;
}

function secondsToHoursAndMinutes(totalSeconds) {
    const clamped = Math.max(0, Math.floor(Number(totalSeconds) || 0));
    const totalMinutes = clamped === 0 ? 0 : Math.max(1, Math.floor(clamped / 60));

    return {
        hours: Math.floor(totalMinutes / 60),
        minutes: totalMinutes % 60,
    };
}

export default function workTimer() {
    let root = null;

    return {
        open: false,
        busy: false,
        title: '',
        error: '',
        active: null,
        recent: [],
        selectedIds: [],
        lastStopped: null,
        baseElapsed: 0,
        tickSeconds: 0,
        syncedAtMs: 0,
        interval: null,
        baseUrl: '/work-sessions',
        createUrl: '/evidence/create',

        init() {
            root = this.$root;
            this.baseUrl = root.dataset.baseUrl || '/work-sessions';
            this.createUrl = root.dataset.createUrl || '/evidence/create';

            let initial;

            try {
                initial = JSON.parse(root.dataset.state || '{}');
            } catch {
                initial = {};
            }

            this.applyState(initial);
        },

        destroy() {
            this.stopTicker();
        },

        applyState(state) {
            if (!state || typeof state !== 'object') {
                return;
            }

            this.active = state.active || null;
            this.recent = Array.isArray(state.recent) ? state.recent : [];

            if (state.create_url) {
                this.createUrl = state.create_url;
            }

            const validIds = new Set(this.recent.map((item) => Number(item.id)));

            this.selectedIds = this.selectedIds.filter((id) => validIds.has(Number(id)));

            if (this.active) {
                this.title = this.active.title || '';
                this.baseElapsed = Math.max(0, Number(this.active.elapsed_seconds) || 0);
                this.tickSeconds = this.baseElapsed;
                this.syncedAtMs = Date.now();

                if (this.active.status === 'running') {
                    this.startTicker();
                } else {
                    this.stopTicker();
                }
            } else {
                this.baseElapsed = 0;
                this.tickSeconds = 0;
                this.stopTicker();
            }
        },

        startTicker() {
            this.stopTicker();
            this.interval = setInterval(() => {
                if (!this.active || this.active.status !== 'running') {
                    return;
                }

                const delta = Math.max(0, Math.floor((Date.now() - this.syncedAtMs) / 1000));

                this.tickSeconds = this.baseElapsed + delta;
            }, 1000);
        },

        stopTicker() {
            if (this.interval !== null) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },

        toggle() {
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },

        onOutsideClick(event) {
            if (root && !root.contains(event.target)) {
                this.close();
            }
        },

        get hasActive() {
            return this.active !== null;
        },

        get isIdle() {
            return this.active === null;
        },

        get isRunning() {
            return this.active !== null && this.active.status === 'running';
        },

        get isPaused() {
            return this.active !== null && this.active.status === 'paused';
        },

        get hasError() {
            return this.error !== '';
        },

        get hasLastStopped() {
            return this.lastStopped !== null;
        },

        get hasRecent() {
            return this.recent.length > 0;
        },

        get hasNoRecent() {
            return this.recent.length === 0;
        },

        get hasSelection() {
            return this.selectedIds.length > 0;
        },

        get activeClock() {
            return formatClock(this.tickSeconds);
        },

        get statusLabel() {
            if (this.isRunning) {
                return 'En curso';
            }

            if (this.isPaused) {
                return 'En pausa';
            }

            return 'Listo';
        },

        get triggerLabel() {
            if (this.isRunning) {
                return `Cronómetro de trabajo (en curso: ${this.activeClock})`;
            }

            if (this.isPaused) {
                return `Cronómetro de trabajo (en pausa: ${this.activeClock})`;
            }

            return 'Cronómetro de trabajo';
        },

        get lastStoppedTitle() {
            return this.lastStopped ? this.lastStopped.title : '';
        },

        get lastStoppedClock() {
            return this.lastStopped ? this.lastStopped.formatted_clock : '00:00:00';
        },

        get lastStoppedUrl() {
            return this.lastStopped ? this.lastStopped.create_evidence_url : this.createUrl;
        },

        get selectedSummaryText() {
            const totalSeconds = this.recent
                .filter((item) => this.selectedIds.includes(Number(item.id)))
                .reduce((sum, item) => sum + (Number(item.elapsed_seconds) || 0), 0);
            const { hours, minutes } = secondsToHoursAndMinutes(totalSeconds);

            return `Crear evidencia con seleccionadas (${hours} h ${minutes} min)`;
        },

        get selectedCreateUrl() {
            const params = new URLSearchParams();

            this.selectedIds.forEach((id) => {
                params.append('sessions[]', String(id));
            });

            const query = params.toString();

            return query ? `${this.createUrl}?${query}` : this.createUrl;
        },

        isSelected(id) {
            return this.selectedIds.includes(Number(id));
        },

        toggleSelect(id) {
            const numericId = Number(id);
            const index = this.selectedIds.indexOf(numericId);

            if (index >= 0) {
                this.selectedIds.splice(index, 1);
            } else {
                this.selectedIds.push(numericId);
            }
        },

        selectLabel(item) {
            return `Seleccionar sesión ${item.title}`;
        },

        deleteLabel(item) {
            return `Eliminar sesión ${item.title}`;
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        async request(url, method, payload = null) {
            this.busy = true;
            this.error = '';

            try {
                const options = {
                    method,
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    credentials: 'same-origin',
                };

                if (payload !== null) {
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(payload);
                }

                const response = await fetch(url, options);
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstError =
                        data?.errors?.title?.[0] ||
                        data?.message ||
                        'No se pudo actualizar el cronómetro.';

                    this.error = firstError;

                    return null;
                }

                this.applyState(data);

                return data;
            } catch {
                this.error = 'Error de conexión al actualizar el cronómetro.';

                return null;
            } finally {
                this.busy = false;
            }
        },

        async start() {
            const trimmed = this.title.trim();

            if (trimmed === '') {
                this.error = 'Escribe un título para la sesión de trabajo.';
                root?.querySelector('#work-timer-title')?.focus();

                return;
            }

            this.lastStopped = null;
            await this.request(this.baseUrl, 'POST', { title: trimmed });
        },

        async pause() {
            if (!this.active) {
                return;
            }

            await this.request(`${this.baseUrl}/${this.active.id}/pause`, 'POST', {
                title: this.title.trim(),
            });
        },

        async resume() {
            if (!this.active) {
                return;
            }

            await this.request(`${this.baseUrl}/${this.active.id}/resume`, 'POST', {
                title: this.title.trim(),
            });
        },

        async stop() {
            if (!this.active) {
                return;
            }

            const data = await this.request(`${this.baseUrl}/${this.active.id}/stop`, 'POST', {
                title: this.title.trim(),
            });

            if (data && data.session) {
                this.lastStopped = data.session;
                this.title = '';
            }
        },

        async removeSession(id) {
            const numericId = Number(id);
            const data = await this.request(`${this.baseUrl}/${numericId}`, 'DELETE');

            if (data && this.lastStopped && Number(this.lastStopped.id) === numericId) {
                this.lastStopped = null;
            }
        },
    };
}
