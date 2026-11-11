{{--
    Topbar work stopwatch: starts, pauses, resumes and stops a titled session stored in the database, keeps ticking
    across navigations and reloads, offers a direct «Crear evidencia» action when stopped, and lists recent sessions
    with checkboxes to combine several sessions into one evidence.
--}}
@props(['state' => []])
@php
    $baseUrl = Route::has('work-sessions.index') ? route('work-sessions.index') : url('/work-sessions');
    $createUrl =
        $state['create_url'] ?? (Route::has('evidence.create') ? route('evidence.create') : url('/evidence/create'));
@endphp
<div x-data="workTimer" data-state="{{ json_encode($state, JSON_THROW_ON_ERROR) }}"
    data-base-url="{{ $baseUrl }}" data-create-url="{{ $createUrl }}" x-on:keydown.escape.prevent="close"
    class="inline-block md:relative">
    <button type="button" id="work-timer-trigger" aria-haspopup="dialog" aria-controls="work-timer-panel"
        aria-expanded="false" x-bind:aria-expanded="open ? 'true' : 'false'" x-on:click="toggle"
        class="inline-flex h-9 items-center gap-1.5 rounded-full px-2.5 text-ink-muted transition hover:bg-glass-2 hover:text-ink pointer-coarse:min-h-11 pointer-coarse:min-w-11">
        <x-lucide-timer class="size-4.5 shrink-0" aria-hidden="true" />
        <span class="sr-only" x-text="triggerLabel">Cronómetro de trabajo</span>
        <span aria-hidden="true" x-show="hasActive" x-text="activeClock"
            class="font-mono text-xs font-semibold tabular-nums text-brand-ink"></span>
    </button>

    <div id="work-timer-panel" role="dialog" aria-label="Cronómetro de trabajo" hidden x-bind:hidden="!open"
        x-on:click.outside="onOutsideClick"
        class="glass absolute inset-x-0 top-[calc(100%+0.5rem)] z-30 grid gap-4 rounded-xl p-4 text-sm text-ink shadow-glow md:inset-x-auto md:right-0 md:w-[22rem]">
        <div class="flex items-center justify-between gap-2">
            <span class="font-bold tracking-tight text-ink">Cronómetro de trabajo</span>
            <span x-text="statusLabel"
                class="rounded-full bg-glass-2 px-2.5 py-0.5 text-2xs font-semibold text-ink-muted"></span>
        </div>

        <div class="grid gap-3">
            <label for="work-timer-title" class="grid gap-1">
                <span class="text-xs font-semibold text-ink">Nombre de la sesión</span>
                <input id="work-timer-title" type="text" x-model="title" x-on:keydown.enter.prevent="start"
                    placeholder="Ej. Montaje del stand" maxlength="255" autocomplete="off"
                    class="h-10 w-full rounded-md border border-field-edge bg-field px-3 text-sm text-ink outline-none placeholder:text-ink-muted focus:border-aurora-500">
            </label>

            <div class="glass-2 flex items-center justify-between gap-3 rounded-lg px-3 py-2.5">
                <span class="text-xs font-medium text-ink-muted">Tiempo acumulado</span>
                <output id="work-timer-display" aria-live="off"
                    class="font-mono text-xl font-bold tabular-nums text-ink" x-text="activeClock">00:00:00</output>
            </div>

            <p x-show="hasError" x-text="error" role="alert" class="text-xs font-semibold text-rejected-ink"></p>

            <div class="flex flex-wrap gap-2">
                <button type="button" x-show="isIdle" x-on:click="start" x-bind:disabled="busy"
                    class="inline-flex h-9 flex-1 items-center justify-center gap-1.5 rounded-full bg-brand px-4 text-xs font-semibold text-on-brand shadow-glow transition hover:bg-brand-lo disabled:opacity-50">
                    <x-lucide-play class="size-3.5" aria-hidden="true" />
                    Iniciar
                </button>
                <button type="button" x-show="isRunning" x-on:click="pause" x-bind:disabled="busy"
                    class="inline-flex h-9 flex-1 items-center justify-center gap-1.5 rounded-full border border-glass-edge bg-glass-2 px-3 text-xs font-semibold text-ink transition hover:bg-glass-hi disabled:opacity-50">
                    <x-lucide-pause class="size-3.5" aria-hidden="true" />
                    Pausar
                </button>
                <button type="button" x-show="isPaused" x-on:click="resume" x-bind:disabled="busy"
                    class="inline-flex h-9 flex-1 items-center justify-center gap-1.5 rounded-full bg-brand px-3 text-xs font-semibold text-on-brand shadow-glow transition hover:bg-brand-lo disabled:opacity-50">
                    <x-lucide-play class="size-3.5" aria-hidden="true" />
                    Reanudar
                </button>
                <button type="button" x-show="hasActive" x-on:click="stop" x-bind:disabled="busy"
                    class="inline-flex h-9 flex-1 items-center justify-center gap-1.5 rounded-full border border-rejected/40 bg-rejected-soft px-3 text-xs font-semibold text-rejected-ink transition hover:opacity-90 disabled:opacity-50">
                    <x-lucide-square class="size-3.5" aria-hidden="true" />
                    Detener
                </button>
            </div>
        </div>

        <div x-show="hasLastStopped" class="grid gap-2 rounded-lg border border-accepted/40 bg-accepted-soft p-3">
            <div class="flex items-center justify-between gap-2 text-xs">
                <span class="truncate font-semibold text-accepted-ink" x-text="lastStoppedTitle"></span>
                <span class="font-mono font-semibold tabular-nums text-accepted-ink" x-text="lastStoppedClock"></span>
            </div>
            <a x-bind:href="lastStoppedUrl"
                class="inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-full bg-brand px-3 text-xs font-semibold text-on-brand no-underline shadow-glow transition hover:bg-brand-lo">
                <x-lucide-file-plus class="size-3.5" aria-hidden="true" />
                Crear evidencia
            </a>
        </div>

        <div class="grid gap-2 border-t border-rule pt-3">
            <span class="text-xs font-bold tracking-wider text-ink-muted uppercase">Sesiones recientes</span>
            <p x-show="hasNoRecent" class="text-xs text-ink-muted">Aún no hay sesiones detenidas.</p>
            <ul x-show="hasRecent" aria-label="Sesiones recientes"
                class="grid max-h-44 gap-1.5 overflow-y-auto pr-0.5 scrollbar-subtle">
                <template x-for="item in recent" x-bind:key="item.id">
                    <li class="glass-2 flex items-center gap-2 rounded-md px-2.5 py-1.5">
                        <input type="checkbox" x-bind:value="item.id" x-bind:checked="isSelected(item.id)"
                            x-on:change="toggleSelect(item.id)" x-bind:aria-label="selectLabel(item)"
                            class="size-4 shrink-0 cursor-pointer accent-aurora-500">
                        <span class="min-w-0 flex-1 truncate text-xs font-medium text-ink" x-text="item.title"></span>
                        <span class="shrink-0 font-mono text-2xs text-ink-muted tabular-nums"
                            x-text="item.formatted_clock"></span>
                        <button type="button" x-on:click="removeSession(item.id)"
                            x-bind:aria-label="deleteLabel(item)" x-bind:title="deleteLabel(item)"
                            class="inline-flex size-6 shrink-0 items-center justify-center rounded-full text-ink-muted transition hover:bg-glass-hi hover:text-rejected-ink">
                            <x-lucide-trash-2 class="size-3.5" aria-hidden="true" />
                        </button>
                    </li>
                </template>
            </ul>
            <a x-show="hasSelection" x-bind:href="selectedCreateUrl" x-text="selectedSummaryText"
                class="mt-1 inline-flex h-9 w-full items-center justify-center rounded-full bg-brand px-3 text-center text-xs font-semibold text-on-brand no-underline shadow-glow transition hover:bg-brand-lo"></a>
        </div>
    </div>
</div>
