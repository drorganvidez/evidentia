{{-- Tables: the evidence list with quick filter and active sort, a compact management list, the activity log with a flagged row and an empty one --}}
@php
    $now = now();
    $hue = fn(int $id): int => (($id * 7) % 12) * 30;
    $statuses = [
        'DRAFT' => ['draft', 'En borrador', false],
        'PENDING' => ['pending', 'Pendiente de revisión', true],
        'ACCEPTED' => ['accepted', 'Aceptada', false],
        'REJECTED' => ['rejected', 'Rechazada', false],
    ];
    $evidences = [
        [
            'id' => 33,
            'title' => 'Montaje del photocall',
            'text' => 'Instalación del fondo y de las luces en el vestíbulo',
            'committee' => [7, 'Logística'],
            'hours' => 1.75,
            'status' => 'PENDING',
            'updated' => $now->copy()->subHours(6),
        ],
        [
            'id' => 31,
            'title' => 'Guion de la mesa redonda',
            'text' => 'Preguntas y tiempos para las tres ponentes',
            'committee' => [3, 'Programa'],
            'hours' => 2.5,
            'status' => 'ACCEPTED',
            'updated' => $now->copy()->subDay(),
        ],
        [
            'id' => 29,
            'title' => 'Hilo de difusión en redes',
            'text' => 'Doce publicaciones programadas para la semana previa',
            'committee' => [8, 'Comunicación'],
            'hours' => 1,
            'status' => 'ACCEPTED',
            'updated' => $now->copy()->subDays(2),
        ],
        [
            'id' => 27,
            'title' => 'Acta de la reunión 3',
            'text' => 'Transcripción de los acuerdos y del turno de preguntas',
            'committee' => [2, 'Secretaría'],
            'hours' => 0.5,
            'status' => 'REJECTED',
            'updated' => $now->copy()->subDays(4),
        ],
        [
            'id' => 24,
            'title' => 'Presupuesto del catering',
            'text' => 'Comparativa de tres proveedores con desglose por día',
            'committee' => [6, 'Finanzas'],
            'hours' => 2,
            'status' => 'ACCEPTED',
            'updated' => $now->copy()->subDays(6),
        ],
        [
            'id' => 21,
            'title' => 'Encuesta de satisfacción',
            'text' => 'Borrador del cuestionario para las asistentes',
            'committee' => [4, 'Igualdad'],
            'hours' => 0.5,
            'status' => 'DRAFT',
            'updated' => $now->copy()->subDays(9),
        ],
    ];
    $evidenceColumns = [
        [
            'label' => 'Evidencia',
            'key' => 'title',
            'sort' => ['href' => route('ui.index', ['sort' => 'title']) . '#ui-table', 'dir' => null],
        ],
        ['label' => 'Comité', 'key' => 'committee', 'hide' => true],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Estado', 'key' => 'status'],
        [
            'label' => 'Actualizada',
            'key' => 'updated',
            'sort' => [
                'href' => route('ui.index', ['sort' => 'updated', 'dir' => 'asc']) . '#ui-table',
                'dir' => 'desc',
            ],
        ],
        ['label' => 'Abrir', 'srOnly' => true],
    ];
    $management = [
        [
            'id' => 118,
            'code' => 'EV-0118',
            'title' => 'Cartelería de las salas',
            'student' => ['Lucía', 'Pérez'],
            'committee' => [3, 'Programa'],
            'hours' => 1.5,
            'status' => 'PENDING',
            'updated' => $now->copy()->subMinutes(40),
        ],
        [
            'id' => 117,
            'code' => 'EV-0117',
            'title' => 'Lista de ponentes confirmadas',
            'student' => ['Carlos', 'Navarro'],
            'committee' => [3, 'Programa'],
            'hours' => 0.75,
            'status' => 'PENDING',
            'updated' => $now->copy()->subHours(3),
        ],
        [
            'id' => 115,
            'code' => 'EV-0115',
            'title' => 'Cronograma del primer día',
            'student' => ['Marta', 'Ruiz'],
            'committee' => [3, 'Programa'],
            'hours' => 2.25,
            'status' => 'ACCEPTED',
            'updated' => $now->copy()->subDays(1),
        ],
        [
            'id' => 112,
            'code' => 'EV-0112',
            'title' => 'Reserva del aula magna',
            'student' => ['Diego', 'Torres'],
            'committee' => [3, 'Programa'],
            'hours' => 0.5,
            'status' => 'REJECTED',
            'updated' => $now->copy()->subDays(3),
        ],
    ];
    $managementColumns = [
        ['label' => 'Registro', 'key' => 'code'],
        [
            'label' => 'Evidencia',
            'key' => 'title',
            'sort' => ['href' => route('ui.index', ['sort' => 'title']) . '#ui-table', 'dir' => null],
        ],
        ['label' => 'Estudiante', 'key' => 'student'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Estado', 'key' => 'status'],
        [
            'label' => 'Actualizada',
            'key' => 'updated',
            'sort' => [
                'href' => route('ui.index', ['sort' => 'updated', 'dir' => 'asc']) . '#ui-table',
                'dir' => 'desc',
            ],
        ],
        ['label' => 'Abrir', 'srOnly' => true],
    ];
    $activity = [
        [
            'at' => $now->copy()->subMinutes(12),
            'who' => ['Lucía', 'Pérez', 'como estudiante'],
            'action' => 'Subió la evidencia',
            'object' => 'Cartelería de las salas',
            'flag' => null,
        ],
        [
            'at' => $now->copy()->subHours(2),
            'who' => ['Carlos', 'Navarro', 'como coordinador de Programa'],
            'action' => 'Aceptó la evidencia',
            'object' => 'Cronograma del primer día',
            'flag' => null,
        ],
        [
            'at' => $now->copy()->subHours(5),
            'who' => ['Elena', 'Martín', 'como presidenta'],
            'action' => 'Firmó el acta',
            'object' => 'Reunión 3 de Programa',
            'flag' => 'Firma fuera de plazo',
        ],
        [
            'at' => $now->copy()->subDay(),
            'who' => ['Marta', 'Ruiz', 'como secretaria de Programa'],
            'action' => 'Convocó la reunión',
            'object' => 'Reunión 4 de Programa',
            'flag' => null,
        ],
    ];
    $activityColumns = [
        ['label' => 'Acción', 'key' => 'action'],
        ['label' => 'Cuándo', 'key' => 'at'],
        ['label' => 'Quién', 'key' => 'who'],
        ['label' => 'Objeto', 'key' => 'object', 'hide' => true],
    ];
@endphp
<section id="ui-table" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-table-title">
    <h2 id="ui-table-title" class="text-2xl font-bold tracking-tight">Tablas</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Listado con filtro rápido</h3>
        <x-ui.table caption="Mis evidencias" :columns="$evidenceColumns" filterable count-label="evidencias"
            filter-placeholder="Filtrar evidencias">
            @foreach ($evidences as $evidence)
                @php
                    [$tone, $label, $pulse] = $statuses[$evidence['status']];
                    [$committeeId, $committeeName] = $evidence['committee'];
                    $href = route('ui.index', ['evidence' => $evidence['id']]) . '#ui-table';
                @endphp
                <x-ui.table.row :href="$href" :search="$evidence['title'] . ' ' . $committeeName . ' ' . $label">
                    <x-ui.table.cell label="Evidencia" primary>
                        <a href="{{ $href }}">{{ $evidence['title'] }}</a>
                        <span
                            class="mt-0.5 block max-w-[40ch] truncate text-sm text-ink-muted md:line-clamp-none">{{ $evidence['text'] }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Comité" hide>
                        <x-ui.badge variant="chip" :hue="$hue($committeeId)">{{ $committeeName }}</x-ui.badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence['hours']" /></x-ui.table.cell>
                    <x-ui.table.cell label="Estado" end>
                        <x-ui.badge :tone="$tone" :pulse="$pulse" size="sm">{{ $label }}</x-ui.badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Actualizada" meta><x-ui.time :value="$evidence['updated']" inline
                            short /></x-ui.table.cell>
                    <x-ui.table.cell actions>
                        <x-ui.button icon-only icon="eye" :label="'Abrir ' . $evidence['title']" :href="$href" variant="ghost"
                            size="sm" dim />
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
            <x-slot:footer>
                <span>6 evidencias · <x-ui.hours :value="8.25" size="sm" class="text-ink" /> declaradas</span>
            </x-slot:footer>
        </x-ui.table>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Gestión, densidad compacta y desplazamiento horizontal</h3>
        <x-ui.table caption="Evidencias de Programa" :columns="$managementColumns" density="compact" :cards="false">
            @foreach ($management as $evidence)
                @php
                    [$tone, $label, $pulse] = $statuses[$evidence['status']];
                    [$committeeId, $committeeName] = $evidence['committee'];
                    [$name, $surname] = $evidence['student'];
                    $href = route('ui.index', ['evidence' => $evidence['id']]) . '#ui-table';
                @endphp
                <x-ui.table.row :href="$href">
                    <x-ui.table.cell label="Registro" header class="whitespace-nowrap">
                        <x-ui.badge variant="chip" mono>{{ $evidence['code'] }}</x-ui.badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Evidencia" primary><a
                            href="{{ $href }}">{{ $evidence['title'] }}</a></x-ui.table.cell>
                    <x-ui.table.cell label="Estudiante"><x-ui.person :name="$name" :surname="$surname"
                            size="sm" /></x-ui.table.cell>
                    <x-ui.table.cell label="Comité">
                        <x-ui.badge variant="chip" :hue="$hue($committeeId)">{{ $committeeName }}</x-ui.badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence['hours']" /></x-ui.table.cell>
                    <x-ui.table.cell label="Estado">
                        <x-ui.badge :tone="$tone" :pulse="$pulse"
                            size="sm">{{ $label }}</x-ui.badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Actualizada" meta><x-ui.time :value="$evidence['updated']" inline
                            short /></x-ui.table.cell>
                    <x-ui.table.cell actions>
                        <x-ui.button icon-only icon="eye" :label="'Abrir ' . $evidence['title']" :href="$href" variant="ghost"
                            size="sm" dim />
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
            <x-slot:footer>
                <span>Mostrando de la 21 a la 30 de 128 evidencias</span>
            </x-slot:footer>
        </x-ui.table>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Registro de actividad con una fila señalada</h3>
        <x-ui.table caption="Registro de actividad" :columns="$activityColumns" show-caption>
            @foreach ($activity as $entry)
                @php
                    [$name, $surname, $role] = $entry['who'];
                @endphp
                <x-ui.table.row :flag="$entry['flag']">
                    <x-ui.table.cell label="Acción" primary>{{ $entry['action'] }}</x-ui.table.cell>
                    <x-ui.table.cell label="Cuándo" meta><x-ui.time :value="$entry['at']" inline short /></x-ui.table.cell>
                    <x-ui.table.cell label="Quién" full>
                        <x-ui.person :name="$name" :surname="$surname" :role="$role" size="sm" />
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Objeto" hide>{{ $entry['object'] }}</x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
        </x-ui.table>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Sin filas</h3>
        <x-ui.table caption="Evidencias rechazadas" :columns="$evidenceColumns">
            <x-slot:empty>
                <x-ui.empty icon="file-x" title="No hay evidencias rechazadas"
                    text="Las que se rechacen aparecerán aquí." size="sm" :level="4" />
            </x-slot:empty>
        </x-ui.table>
    </div>
</section>
