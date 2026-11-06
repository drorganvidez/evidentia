{{-- Committees of the course: their people and records, a new committee, and the name and icon of each one --}}
@php
    $columns = [
        ['label' => 'Comité', 'key' => 'name'],
        ['label' => 'Coordinadores', 'key' => 'coordinators', 'align' => 'end'],
        ['label' => 'Secretarios', 'key' => 'secretaries', 'align' => 'end'],
        ['label' => 'Evidencias', 'key' => 'evidences', 'align' => 'end'],
        ['label' => 'Reuniones', 'key' => 'meetings', 'align' => 'end'],
        ['label' => 'Acciones', 'srOnly' => true],
    ];
    $newFields = ['name' => 'Nombre', 'icon' => 'Icono'];
@endphp
<x-layout.app title="Comités" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Comités']]">
    <x-ui.page-header title="Comités">
        <x-slot:actions>
            <x-ui.dialog id="new-committee" title="Nuevo comité" size="lg" trigger="Nuevo comité"
                trigger-variant="primary" trigger-icon="plus" :open-on-load="$errors->hasAny(array_keys($newFields))">
                <x-ui.form :action="$route_new" id="new-committee-form" :fields="$newFields" busy-label="Creando">
                    <x-ui.field name="name" label="Nombre" required autocomplete="off" />
                    <x-ui.icon-picker name="icon" label="Icono" :columns="8" />
                    <x-slot:actions>
                        <x-ui.button type="submit" variant="primary" icon="plus">Crear comité</x-ui.button>
                    </x-slot:actions>
                </x-ui.form>
            </x-ui.dialog>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Comités del curso" :columns="$columns">
        @if ($committees->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="network" title="No hay comités" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($committees as $committee)
            <x-ui.table.row>
                @php
                    $counts = [
                        [$committee->coordinators->count(), 'coordinador', 'coordinadores'],
                        [$committee->secretaries->count(), 'secretario', 'secretarios'],
                        [$committee->evidences->count(), 'evidencia', 'evidencias'],
                        [$committee->meetings->count(), 'reunión', 'reuniones'],
                    ];
                @endphp
                <x-ui.table.cell label="Comité" primary>
                    <x-ui.committee-chip :committee="$committee" size="md" />
                    <span class="mt-0.5 block text-sm text-ink-muted md:hidden">
                        {{ collect($counts)->map(fn($count) => $count[0] . ' ' . ($count[0] === 1 ? $count[1] : $count[2]))->implode(' · ') }}
                    </span>
                </x-ui.table.cell>
                @foreach ($counts as [$count, $one, $many])
                    <x-ui.table.cell :label="ucfirst($many)" numeric meta hide>{{ $count }}</x-ui.table.cell>
                @endforeach
                <x-ui.table.cell end class="w-[1%] whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1">
                        <x-ui.dialog :id="'edit-committee-' . $committee->id" title="Editar el comité" size="lg" :trigger="'Editar ' . $committee->name"
                            trigger-variant="ghost" trigger-icon="pen-line" trigger-icon-only>
                            <form id="edit-committee-form-{{ $committee->id }}" method="post"
                                action="{{ $route }}" class="grid grid-cols-[minmax(0,1fr)] gap-5">
                                @csrf
                                {{-- An icon stored before the picker (Font Awesome markup) stays unless another is chosen --}}
                                <input type="hidden" name="icon_{{ $committee->id }}" value="{{ $committee->icon }}">
                                <x-ui.field :name="'name_' . $committee->id" label="Nombre" :value="$committee->name" required
                                    autocomplete="off" />
                                <x-ui.icon-picker :name="'icon_' . $committee->id" label="Icono" :value="$committee->icon"
                                    :columns="8" />
                            </form>
                            <x-slot:footer>
                                <x-ui.button x-on:click="close">Cancelar</x-ui.button>
                                <x-ui.button variant="primary" type="submit"
                                    form="edit-committee-form-{{ $committee->id }}" icon="save">Guardar</x-ui.button>
                            </x-slot:footer>
                        </x-ui.dialog>
                        @if ($committee->can_be_removed())
                            <x-ui.dialog :id="'remove-committee-' . $committee->id" variant="alert" title="Eliminar el comité"
                                :description="'Se eliminará ' . $committee->name . '. Esta acción no se puede deshacer.'" :trigger="'Eliminar ' . $committee->name" trigger-variant="ghost" trigger-icon="trash-2"
                                trigger-icon-only>
                                <x-slot:footer>
                                    <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                                    <form method="post" action="{{ $route_remove }}">
                                        @csrf
                                        <input type="hidden" name="_id" value="{{ $committee->id }}">
                                        <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí,
                                            eliminar</x-ui.button>
                                    </form>
                                </x-slot:footer>
                            </x-ui.dialog>
                        @endif
                    </div>
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
    </x-ui.table>
</x-layout.app>
