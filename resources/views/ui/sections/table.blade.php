{{--
    Tables: the evidence list with quick filter and the order of the server (ListSort over the demo rows), a list of 128
    paginated and searched on the server, a compact management list, a list ordered in the browser, the activity log with
    a flagged row and an empty one
--}}
@use('App\Support\ListPage')
@use('App\Support\ListSort')
@use('Illuminate\Pagination\LengthAwarePaginator')
@php
    $now = now();
    $link = fn(array $query): string => route('ui.index', $query) . '#ui-table';
    $evidenceFilters = [
        ['label' => 'Todas', 'href' => $link(['estado' => 'todas']), 'count' => 6, 'current' => true],
        ['label' => 'Pendientes', 'href' => $link(['estado' => 'pendientes']), 'count' => 1],
        ['label' => 'Aceptadas', 'href' => $link(['estado' => 'aceptadas']), 'count' => 3],
        ['label' => 'Rechazadas', 'href' => $link(['estado' => 'rechazadas']), 'count' => 1],
        ['label' => 'Borradores', 'href' => $link(['estado' => 'borradores']), 'count' => 1],
    ];
    $managementPages = new LengthAwarePaginator(range(1, 10), 128, 10, 3, [
        'path' => route('ui.index'),
        'pageName' => 'pagina',
        'fragment' => 'ui-table',
    ]);
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
    // The order of the server: allowed keys, dates by their timestamp, links that keep the query string
    $sort = ListSort::from(
        request(),
        [
            'title' => 'title',
            'committee' => 'committee.1',
            'hours' => 'hours',
            'status' => ListSort::rank('status', ['DRAFT', 'PENDING', 'ACCEPTED', 'REJECTED']),
            'updated' => ListSort::desc('updated'),
        ],
        'updated',
        path: route('ui.index'),
    );
    $evidences = $sort->apply(collect($evidences))->all();
    $evidenceColumns = array_map(
        fn(array $column): array => isset($column['sort'])
            ? [
                    'sort' => [
                        'href' => $column['sort']['href'] . '#ui-table',
                        'dir' => $column['sort']['dir'],
                        'asc' => $column['sort']['asc'] . '#ui-table',
                        'desc' => $column['sort']['desc'] . '#ui-table',
                    ],
                ] + $column
            : $column,
        $sort->columns([
            ['label' => 'Evidencia', 'key' => 'title'],
            ['label' => 'Comité', 'key' => 'committee', 'hide' => true],
            ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
            ['label' => 'Estado', 'key' => 'status'],
            ['label' => 'Actualizada', 'key' => 'updated'],
            ['label' => 'Abrir', 'srOnly' => true],
        ]),
    );
    $signups = [
        ['Lucía', 'Pérez', $now->copy()->subMinutes(10), 2, 'Checked In'],
        ['Carlos', 'Navarro', $now->copy()->subHours(2), 0.5, 'Attending'],
        ['Elena', 'Álvarez', $now->copy()->subDays(3), 10, 'Not Attending'],
        ['Marta', 'Gómez', $now->copy()->subMonth(), 1.25, 'Guests Attended'],
        ['Diego', 'Torres', null, null, 'Not Attending (Refunded/Canceled)'],
    ];
    $signupColumns = [
        ['label' => 'Persona', 'key' => 'person'],
        ['label' => 'Inscrita', 'key' => 'signed', 'sorted' => 'desc'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Estado', 'key' => 'status'],
        ['label' => 'Acciones', 'srOnly' => true],
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
        ['label' => 'Cuándo', 'key' => 'at', 'sorted' => 'desc'],
        ['label' => 'Quién', 'key' => 'who'],
        ['label' => 'Objeto', 'key' => 'object', 'hide' => true],
    ];
    // 128 evidences, newest first, searched and paginated on the server from the query string of the catalogue
    $topics = [
        'Inventario del almacén',
        'Turno de acreditaciones',
        'Boletín semanal',
        'Reserva de salas',
        'Traducción de carteles',
        'Visita guiada',
    ];
    $students = [['Lucía', 'Pérez'], ['Juan', 'García'], ['Marta', 'Ruiz'], ['Pablo', 'Torres'], ['Elena', 'Moreno']];
    $pagedCommittees = [[3, 'Programa'], [7, 'Logística'], [5, 'Comunicación']];
    $plain = fn(string $text): string => Str::lower(Str::ascii($text));
    $words = array_filter(explode(' ', $plain(ListPage::term(request()) ?? '')));
    $pagedRows = collect(range(128, 1))
        ->map(
            fn(int $n): array => [
                'id' => 1000 + $n,
                'title' => $topics[$n % 6] . ' ' . $n,
                'student' => $students[$n % 5],
                'committee' => $pagedCommittees[$n % 3],
                'hours' => [0.5, 1, 1.5, 2][$n % 4],
                'status' => ['PENDING', 'ACCEPTED', 'REJECTED', 'ACCEPTED'][$n % 4],
                'updated' => $now->copy()->subHours(129 - $n),
            ],
        )
        ->filter(
            fn(array $row): bool => collect($words)->every(
                fn(string $word): bool => str_contains(
                    $plain($row['title'] . ' ' . implode(' ', $row['student']) . ' ' . $row['committee'][1]),
                    $word,
                ),
            ),
        )
        ->values();
    $pagedHours = $pagedRows->sum('hours');
    $pagedEvidences = ListPage::paginate($pagedRows, request())->fragment('paged-evidences');
    $pagedColumns = [
        ['label' => 'Evidencia', 'key' => 'title'],
        ['label' => 'Estudiante', 'key' => 'student'],
        ['label' => 'Comité', 'key' => 'committee', 'hide' => true],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Estado', 'key' => 'status'],
    ];
@endphp
<section id="ui-table" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-table-title">
    <h2 id="ui-table-title" class="text-2xl font-bold tracking-tight">Tablas</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Listado con filtro rápido</h3>
        <x-ui.table caption="Mis evidencias" :columns="$evidenceColumns" filterable count-label="evidencias"
            filter-placeholder="Filtrar evidencias">
            <x-slot:toolbar>
                <x-ui.tabs label="Filtrar evidencias por estado" :items="$evidenceFilters" />
            </x-slot:toolbar>
            @foreach ($evidences as $evidence)
                @php
                    [$committeeId, $committeeName] = $evidence['committee'];
                    $href = route('ui.index', ['evidence' => $evidence['id']]) . '#ui-table';
                @endphp
                <x-ui.table.row :href="$href">
                    <x-ui.table.cell label="Evidencia" primary>
                        <a href="{{ $href }}">{{ $evidence['title'] }}</a>
                        <span
                            class="mt-0.5 block max-w-[40ch] truncate text-sm text-ink-muted md:line-clamp-none">{{ $evidence['text'] }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Comité" hide>
                        <x-ui.committee-chip :name="$committeeName" :id="$committeeId" />
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence['hours']" /></x-ui.table.cell>
                    <x-ui.table.cell label="Estado" end>
                        <x-ui.evidence-status :status="$evidence['status']" size="sm" />
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
        <h3 class="text-lg font-bold tracking-tight">Listado paginado con búsqueda en el servidor</h3>
        <x-ui.table id="paged-evidences" caption="Evidencias en páginas" :columns="$pagedColumns" :paginator="$pagedEvidences"
            filterable count-label="evidencias" count-singular="evidencia" filter-placeholder="Buscar evidencias">
            @foreach ($pagedEvidences as $evidence)
                @php
                    [$name, $surname] = $evidence['student'];
                    [$committeeId, $committeeName] = $evidence['committee'];
                    $href = route('ui.index', ['evidence' => $evidence['id']]) . '#paged-evidences';
                @endphp
                <x-ui.table.row :href="$href">
                    <x-ui.table.cell label="Evidencia" primary><a
                            href="{{ $href }}">{{ $evidence['title'] }}</a></x-ui.table.cell>
                    <x-ui.table.cell label="Estudiante"><x-ui.person :name="$name" :surname="$surname"
                            size="sm" /></x-ui.table.cell>
                    <x-ui.table.cell label="Comité" hide>
                        <x-ui.committee-chip :name="$committeeName" :id="$committeeId" />
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence['hours']" /></x-ui.table.cell>
                    <x-ui.table.cell label="Estado" end>
                        <x-ui.evidence-status :status="$evidence['status']" size="sm" />
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
            @if ($pagedEvidences->isEmpty())
                <x-slot:empty>
                    <x-ui.empty icon="list-checks" title="Ninguna evidencia coincide con la búsqueda" size="sm"
                        :level="4" />
                </x-slot:empty>
            @else
                <x-slot:footer>
                    <span>{{ $pagedEvidences->total() }}
                        {{ $pagedEvidences->total() === 1 ? 'evidencia' : 'evidencias' }} ·
                        <x-ui.hours :value="$pagedHours" size="sm" class="text-ink" /></span>
                </x-slot:footer>
            @endif
        </x-ui.table>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Gestión, densidad compacta y desplazamiento horizontal</h3>
        <x-ui.table caption="Evidencias de Programa" :columns="$managementColumns" density="compact" :cards="false">
            @foreach ($management as $evidence)
                @php
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
                        <x-ui.committee-chip :name="$committeeName" :id="$committeeId" />
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence['hours']" /></x-ui.table.cell>
                    <x-ui.table.cell label="Estado">
                        <x-ui.evidence-status :status="$evidence['status']" size="sm" />
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
                <x-ui.pagination :paginator="$managementPages" label="Paginación de las evidencias de Programa" />
            </x-slot:footer>
        </x-ui.table>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Orden en el navegador</h3>
        <x-ui.table id="ui-table-sortable" caption="Inscripciones de la jornada" :columns="$signupColumns" sortable filterable
            count-label="inscripciones" filter-placeholder="Filtrar inscripciones">
            @foreach ($signups as [$name, $surname, $signed, $hours, $status])
                <x-ui.table.row>
                    <x-ui.table.cell label="Persona" primary>
                        <x-ui.person :name="$name" :surname="$surname" size="sm" reverse />
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Inscrita" meta><x-ui.time :value="$signed"
                            format="relative" /></x-ui.table.cell>
                    <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$hours" /></x-ui.table.cell>
                    <x-ui.table.cell label="Estado" end><x-ui.attendee-status :status="$status"
                            size="sm" /></x-ui.table.cell>
                    <x-ui.table.cell actions>
                        <x-ui.button icon-only icon="mail" :label="'Escribir a ' . $name . ' ' . $surname" href="#ui-table-sortable"
                            variant="ghost" size="sm" dim />
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
        </x-ui.table>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Registro de actividad con una fila señalada</h3>
        <x-ui.table caption="Registro de actividad" :columns="$activityColumns" show-caption sortable>
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
