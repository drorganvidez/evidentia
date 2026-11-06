{{-- Every attribute expression the components use, run live under the strict policy --}}
<section id="ui-csp" class="glass grid gap-6 rounded-xl p-6 md:p-8" aria-labelledby="ui-csp-title"
    x-data="probe" x-on:keydown.window="onKey" x-on:ui-notice.window="receive">
    <h2 id="ui-csp-title" class="text-2xl font-bold tracking-tight">Sonda CSP</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-2">
        <div class="glass-2 grid grid-cols-[minmax(0,1fr)] gap-3 rounded-lg p-4" data-probe="loops">
            <ol class="grid grid-cols-[minmax(0,1fr)] gap-2">
                <template x-for="(item, index) in items" x-bind:key="item.key">
                    <li class="grid grid-cols-[minmax(0,1fr)] gap-2 rounded-md border border-rule p-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-sm text-ink-muted" x-text="index + 1"></span>
                            <input type="text" x-model="item.title" aria-label="Título"
                                class="w-0 min-w-0 flex-1 rounded-xs border border-field-edge bg-field px-2 py-1 text-control">
                            <input type="number" x-model.number="item.duration" aria-label="Duración"
                                class="w-20 rounded-xs border border-field-edge bg-field px-2 py-1 text-control">
                            <span class="text-sm text-pending-ink" x-show="item.duration === 20">veinte</span>
                            <button type="button" x-on:click="remove(index)" x-bind:disabled="index === 0"
                                class="rounded-full px-2 py-1 text-sm font-semibold text-rejected-ink disabled:opacity-50">Quitar</button>
                        </div>
                        <ul class="grid grid-cols-[minmax(0,1fr)] gap-1 pl-6">
                            <template x-for="(child, j) in item.children" x-bind:key="child.key">
                                <li class="flex min-w-0 items-center gap-2">
                                    <input type="text" x-model="child.title" aria-label="Hijo"
                                        class="w-0 min-w-0 flex-1 rounded-xs border border-field-edge bg-field px-2 py-1 text-control">
                                    <button type="button" x-on:click="removeChild(index, j)"
                                        class="rounded-full px-2 py-1 text-sm font-semibold text-rejected-ink">Quitar
                                        hijo</button>
                                </li>
                            </template>
                        </ul>
                    </li>
                </template>
            </ol>
            <div class="flex items-center gap-2">
                <input type="text" x-model="query" x-on:input.debounce.150ms="filter"
                    x-on:keydown.enter.prevent="add" placeholder="Escribe y pulsa Intro" aria-label="Nuevo"
                    class="w-0 min-w-0 flex-1 rounded-xs border border-field-edge bg-field px-2 py-1 text-control">
                <span class="font-mono text-sm" data-probe="filtered" x-text="filtered"></span>
            </div>
            <output class="font-mono text-xs text-ink-muted" data-probe="summary" x-text="summary"></output>
        </div>

        <div class="glass-2 grid grid-cols-[minmax(0,1fr)] gap-3 rounded-lg p-4" data-probe="misc">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" x-on:click="$store.theme.select('dark')"
                    class="rounded-full bg-brand px-3 py-1.5 text-sm font-semibold text-on-brand">Tema oscuro</button>
                <button type="button" x-on:click="$store.theme.select('system')"
                    class="glass-2 rounded-full px-3 py-1.5 text-sm font-semibold">Tema sistema</button>
                <span class="font-mono text-sm" data-probe="store" x-text="$store.theme.mode"></span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" x-on:click="$dispatch('ui-notice', { tone: 'accepted', text: 'Guardado' })"
                    class="glass-2 rounded-full px-3 py-1.5 text-sm font-semibold">Avisar</button>
                <span class="font-mono text-sm" data-probe="last" x-text="last"></span>
            </div>
            <div class="flex flex-wrap items-center gap-2" x-on:click.outside="close">
                <button type="button" x-on:click="toggle" x-bind:aria-expanded="open ? 'true' : 'false'"
                    class="glass-2 rounded-full px-3 py-1.5 text-sm font-semibold">Desplegar</button>
                <span x-show="open" x-cloak x-id="['panel']" x-bind:id="$id('panel')" data-probe="panel"
                    class="text-sm">Abierto</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" x-on:click="increment"
                    class="glass-2 rounded-full px-3 py-1.5 text-sm font-semibold">Sumar</button>
                <span class="font-mono text-sm" x-bind:class="ok ? 'text-ink' : 'text-rejected-ink'" data-probe="count"
                    x-text="count"></span>
                <span class="h-2 w-40 overflow-hidden rounded-full bg-rule" aria-hidden="true"><span
                        class="block h-full rounded-full bg-brand" data-probe="bar"
                        x-bind:style="barStyle"></span></span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" x-on:click="$refs.dialog.showModal()"
                    class="glass-2 rounded-full px-3 py-1.5 text-sm font-semibold">Abrir diálogo</button>
                <dialog x-ref="dialog" class="glass m-auto rounded-xl p-6 text-ink backdrop:bg-slate-950/40">
                    <p>Diálogo nativo</p>
                    <form method="dialog" class="mt-4">
                        <button type="submit"
                            class="glass-2 rounded-full px-3 py-1.5 text-sm font-semibold">Cerrar</button>
                    </form>
                </dialog>
            </div>
            <fieldset class="flex flex-wrap items-center gap-3">
                <legend class="text-sm font-semibold">Radios</legend>
                @foreach (['light' => 'Claro', 'dark' => 'Oscuro'] as $value => $label)
                    <label class="inline-flex items-center gap-1.5 text-sm">
                        <input type="radio" name="probe-mode" value="{{ $value }}"
                            x-bind:checked="is('{{ $value }}')" x-on:change="select('{{ $value }}')">
                        {{ $label }}
                    </label>
                @endforeach
                <span class="font-mono text-sm" data-probe="mode" x-text="mode"></span>
            </fieldset>
        </div>
    </div>
</section>
