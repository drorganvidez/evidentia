{{-- Dialogs: a modal with a form, the alert that confirms a deletion, a long modal that scrolls, a drawer and a custom trigger --}}
@php
    $here = route('ui.index') . '#ui-dialog';
@endphp
<section id="ui-dialog" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-dialog-title">
    <h2 id="ui-dialog-title" class="text-2xl font-bold tracking-tight">Diálogos</h2>

    <div class="flex flex-wrap items-center gap-3">
        <div data-demo="modal">
            <x-ui.dialog id="demo-reject" title="Rechazar la evidencia"
                description="El estudiante verá el motivo y podrá corregirla." trigger="Rechazar"
                trigger-icon="circle-x">
                <form id="demo-reject-form" method="get" action="{{ $here }}" class="grid gap-2">
                    <label for="demo-reject-reason" class="text-sm font-semibold">Motivo</label>
                    <textarea id="demo-reject-reason" name="reason" rows="3"
                        class="w-full rounded-sm border border-field-edge bg-field px-3 py-2 text-control text-ink focus:border-aurora-500 focus:outline-none focus:ring-4 focus:ring-aurora-500/18"></textarea>
                </form>
                <x-slot:footer>
                    <x-ui.button x-on:click="close">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" type="submit" form="demo-reject-form"
                        icon="circle-x">Rechazar</x-ui.button>
                </x-slot:footer>
            </x-ui.dialog>
        </div>

        <div data-demo="alert">
            <x-ui.dialog id="demo-remove" variant="alert" title="Eliminar la evidencia"
                description="Se borrarán también sus pruebas. Esta acción no se puede deshacer."
                trigger="Eliminar la evidencia" trigger-icon="trash-2" trigger-icon-only>
                <x-slot:footer>
                    <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                    <form method="get" action="{{ $here }}">
                        <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, eliminar</x-ui.button>
                    </form>
                </x-slot:footer>
            </x-ui.dialog>
        </div>

        <div data-demo="long">
            <x-ui.dialog id="demo-long" title="Condiciones de participación" size="lg"
                trigger="Leer las condiciones" trigger-icon="file-text">
                <div class="grid gap-3 text-md text-ink-muted">
                    @for ($i = 1; $i <= 8; $i++)
                        <p>Párrafo {{ $i }}. Las horas acreditadas se calculan a partir de las evidencias
                            aceptadas,
                            las reuniones con acta firmada y las asistencias registradas. El comité puede pedir
                            correcciones antes de aceptar una evidencia; mientras tanto, la evidencia consta como
                            pendiente y no computa.</p>
                    @endfor
                </div>
                <x-slot:footer>
                    <x-ui.button variant="primary" x-on:click="close">Entendido</x-ui.button>
                </x-slot:footer>
            </x-ui.dialog>
        </div>

        <div data-demo="drawer">
            <x-ui.dialog id="demo-drawer" variant="drawer" title="Menú" trigger="Abrir el cajón" trigger-icon="menu">
                <nav aria-label="Navegación del cajón de ejemplo">
                    <ul class="grid gap-0.75">
                        @foreach (['Inicio' => 'layout-dashboard', 'Mis evidencias' => 'file-text', 'Mis reuniones' => 'handshake', 'Mi perfil' => 'user-round'] as $label => $icon)
                            <li>
                                <a href="{{ $here }}"
                                    class="flex items-center gap-3 rounded-sm px-3 py-2.5 text-md font-medium text-ink no-underline transition hover:bg-glass-2 pointer-coarse:min-h-11">
                                    <x-ui.icon :name="$icon" class="size-4.5" />
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </x-ui.dialog>
        </div>

        <div data-demo="custom-trigger">
            <x-ui.dialog id="demo-custom" title="Firmar el acta" size="sm"
                description="La firma queda registrada con la hora.">
                <x-slot:trigger>
                    <x-ui.button variant="ghost" icon="pen-line" aria-haspopup="dialog" aria-controls="demo-custom"
                        aria-expanded="false" x-bind:aria-expanded="open ? 'true' : 'false'"
                        x-on:click="show">Firmar</x-ui.button>
                </x-slot:trigger>
                <x-slot:footer>
                    <x-ui.button x-on:click="close">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" x-on:click="close" icon="pen-line">Firmar</x-ui.button>
                </x-slot:footer>
            </x-ui.dialog>
        </div>
    </div>
</section>
