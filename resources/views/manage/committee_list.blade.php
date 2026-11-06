{{--
    Committees of the course with their team, queue, hours, meetings, latest activity and signals, each one linked to its
    page; a new committee, and the name and icon of each one.
--}}
@php
    $columns = [
        ['label' => 'Comité', 'key' => 'name'],
        ['label' => 'Equipo', 'key' => 'team'],
        ['label' => 'Pendientes', 'key' => 'pending', 'align' => 'end'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Reuniones', 'key' => 'meetings', 'align' => 'end'],
        ['label' => 'Última actividad', 'key' => 'last'],
        ['label' => 'Alertas', 'key' => 'signals'],
        ['label' => 'Acciones', 'srOnly' => true],
    ];
    $plural = fn(int $count, string $one, string $many): string => $count . ' ' . ($count === 1 ? $one : $many);
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

    <x-ui.table caption="Comités del curso" :columns="$columns" sortable>
        @if ($committees->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="network" title="No hay comités" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($committees as $committee)
            @php
                $summary = $summaries->get($committee->id);
                $view = route($prefix . '.committee.view', ['id' => $committee->id]);
                $people = $summary['presidents']
                    ->merge($summary['coordinators'])
                    ->map(fn($user) => trim($user->name . ' ' . $user->surname))
                    ->merge($summary['secretaries']->map(fn($user) => trim($user->name . ' ' . $user->surname)));
                $team = $summary['coordinators']
                    ->merge($summary['secretaries'])
                    ->map(fn($user) => trim($user->surname . ' ' . $user->name))
                    ->implode(' ');
                $worst = collect($summary['signals'])
                    ->pluck('tone')
                    ->sortBy(fn($tone) => array_search($tone, ['rejected', 'pending', 'notice'], true))
                    ->first();
            @endphp
            <x-ui.table.row :search="implode(' ', [$committee->name, $people->implode(' ')])">
                <x-ui.table.cell label="Comité" primary>
                    <x-ui.committee-chip :committee="$committee" size="md" :href="$view" />
                </x-ui.table.cell>
                <x-ui.table.cell label="Equipo" hide :sort-value="$team">
                    <span class="grid gap-0.5 text-sm">
                        @foreach ($summary['presidents'] as $user)
                            <span>{{ trim($user->name . ' ' . $user->surname) }} <span class="text-ink-muted">·
                                    presidencia</span></span>
                        @endforeach
                        @forelse ($summary['coordinators'] as $user)
                            <span>{{ trim($user->name . ' ' . $user->surname) }} <span class="text-ink-muted">·
                                    coordinación</span></span>
                        @empty
                            @if ($summary['presidents']->isEmpty())
                                <span class="text-rejected-ink">Sin coordinación</span>
                            @endif
                        @endforelse
                        @forelse ($summary['secretaries'] as $user)
                            <span>{{ trim($user->name . ' ' . $user->surname) }} <span class="text-ink-muted">·
                                    secretaría</span></span>
                        @empty
                            <span class="text-pending-ink">Sin secretaría</span>
                        @endforelse
                    </span>
                </x-ui.table.cell>
                <x-ui.table.cell label="Pendientes" numeric :sort-value="$summary['pending']">
                    {{ $summary['pending'] }}
                    @if ($summary['pendingDays'] !== null && $summary['pendingDays'] > 0)
                        <span
                            class="block text-sm text-ink-muted">{{ $plural($summary['pendingDays'], 'día', 'días') }}</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$summary['hours']" /></x-ui.table.cell>
                <x-ui.table.cell label="Reuniones" numeric meta hide>{{ $summary['meetings'] }}</x-ui.table.cell>
                <x-ui.table.cell label="Última actividad" meta hide :sort-value="$summary['last'] ?? ''">
                    @if ($summary['last'])
                        <x-ui.time :value="$summary['last']" format="relative" />
                    @else
                        Ninguna
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Alertas" :sort-value="count($summary['signals'])">
                    @if ($summary['signals'] === [])
                        <x-ui.badge tone="accepted" size="sm">Todo en orden</x-ui.badge>
                    @else
                        <x-ui.badge :tone="$worst"
                            size="sm">{{ $plural(count($summary['signals']), 'alerta', 'alertas') }}</x-ui.badge>
                    @endif
                </x-ui.table.cell>
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
                                    form="edit-committee-form-{{ $committee->id }}"
                                    icon="save">Guardar</x-ui.button>
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
