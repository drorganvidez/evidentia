{{-- Menus: the actions menu with links, a divider and a danger command, the user menu with an avatar trigger, and start alignment --}}
@php
    $here = route('ui.index') . '#ui-menu';
@endphp
{{-- relative z-10: the glass blur makes the section a stacking context, so its popups would paint under the next card --}}
<section id="ui-menu" class="glass relative z-10 grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-menu-title">
    <h2 id="ui-menu-title" class="text-2xl font-bold tracking-tight">Menús</h2>

    <div class="flex flex-wrap items-start gap-x-8 gap-y-4">
        <div class="grid gap-2" data-demo="actions">
            <span class="font-mono text-2xs text-ink-muted">icon-only · divider · danger · method</span>
            <x-ui.menu label="Acciones" icon-only id="demo-actions">
                <x-ui.menu.item href="{{ $here }}" icon="import">Importación masiva</x-ui.menu.item>
                <x-ui.menu.item href="{{ $here }}" icon="download">Exportar</x-ui.menu.item>
                <x-ui.menu.item href="{{ $here }}" icon="shuffle">Aleatorizar</x-ui.menu.item>
                <x-ui.menu.item divider />
                <x-ui.menu.item tone="danger" icon="trash-2" method="post" action="{{ $here }}">Borrar todos
                    los
                    usuarios</x-ui.menu.item>
            </x-ui.menu>
        </div>

        <div class="grid gap-2" data-demo="user">
            <span class="font-mono text-2xs text-ink-muted">trigger con avatar · checked · Salir con
                method="post"</span>
            <x-ui.menu label="Menú de usuario" id="demo-user">
                <x-slot:trigger>
                    <button type="button" x-ref="button" aria-haspopup="menu" aria-controls="demo-user"
                        aria-expanded="false" x-bind:aria-expanded="open ? 'true' : 'false'" x-on:click="toggle"
                        x-on:keydown.down.prevent="openAndFocusFirst" x-on:keydown.up.prevent="openAndFocusLast"
                        class="grid size-9 place-items-center rounded-full transition hover:bg-glass-2 pointer-coarse:size-11">
                        <x-ui.avatar name="Juan" surname="Pérez" />
                        <span class="sr-only">Menú de Juan Pérez</span>
                    </button>
                </x-slot:trigger>
                <li role="none" class="grid px-3 py-2 text-sm">
                    <span class="font-semibold text-ink">Juan Pérez</span>
                    <span class="text-xs text-ink-muted">Estudiante · Coordinador de Programa</span>
                </li>
                <x-ui.menu.item divider />
                <x-ui.menu.item href="{{ $here }}" icon="user-round">Mi perfil</x-ui.menu.item>
                <x-ui.menu.item href="{{ $here }}" icon="clock">Mis horas</x-ui.menu.item>
                <x-ui.menu.item icon="keyboard" :checked="true"
                    x-bind:aria-checked="$store.shortcuts.enabled ? 'true' : 'false'"
                    x-on:click="$store.shortcuts.toggle()">Atajos de teclado</x-ui.menu.item>
                <x-ui.menu.item divider />
                <x-ui.menu.item icon="log-out" method="post" action="{{ $here }}">Salir</x-ui.menu.item>
            </x-ui.menu>
        </div>

        <div class="grid gap-2" data-demo="start">
            <span class="font-mono text-2xs text-ink-muted">align="start" · botones de comando</span>
            <x-ui.menu label="Ordenar" icon="arrow-down-up" align="start" id="demo-sort">
                <x-ui.menu.item icon="calendar">Por fecha</x-ui.menu.item>
                <x-ui.menu.item icon="clock">Por horas</x-ui.menu.item>
                <x-ui.menu.item icon="file-text">Por título</x-ui.menu.item>
            </x-ui.menu>
        </div>
    </div>
</section>
