{{--
    Every evidence sent in the course, for the lecturer and the president: one tab per state (late ones are pending
    longer than they should), filters by committee and dates, and how long each pending one has waited.
--}}
@php
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $list = route($prefix . '.evidence.list');
    $kept = array_filter([
        'comite' => $filters['comite'],
        'desde' => $filters['desde']?->format('Y-m-d'),
        'hasta' => $filters['hasta']?->format('Y-m-d'),
    ]);
    $active = count($kept);
    $tabs = [
        ['Todas', null, $counts->except('LATE')->sum()],
        ['Pendientes', 'pendientes', $counts->get('PENDING', 0)],
        ['Atrasadas', 'atrasadas', $counts->get('LATE', 0)],
        ['Aceptadas', 'aceptadas', $counts->get('ACCEPTED', 0)],
        ['Rechazadas', 'rechazadas', $counts->get('REJECTED', 0)],
    ];
    // Built here: blade-formatter cannot parse a query string inside an attribute expression
    $tabItems = array_map(
        fn(array $tab): array => [
            'label' => $tab[0],
            'href' =>
                $list .
                (($query = http_build_query(array_filter(['estado' => $tab[1]]) + $kept)) === '' ? '' : '?' . $query),
            'count' => $tab[2],
            'current' => $state === $tab[1],
        ],
        $tabs,
    );
    $reset = $list . ($state === null ? '' : '?' . http_build_query(['estado' => $state]));
    $empty = match ($state) {
        'pendientes' => 'No hay evidencias pendientes',
        'aceptadas' => 'No hay evidencias aceptadas',
        'rechazadas' => 'No hay evidencias rechazadas',
        'atrasadas' => 'No hay evidencias atrasadas',
        default => 'No hay evidencias',
    };
    $columns = [
        ['label' => 'Evidencia', 'key' => 'title'],
        ['label' => 'Autor', 'key' => 'author'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Creada', 'key' => 'created_at'],
        ['label' => 'Estado', 'key' => 'status'],
    ];
@endphp
<x-layout.app title="Evidencias" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Evidencias']]">
    <x-ui.page-header title="Evidencias" />

    <x-ui.filter-bar :action="$list" label="Filtros de evidencias" :open="$active > 0" :active="$active"
        :reset-href="$reset" id="evidence-filters">
        @if ($state !== null)
            <input type="hidden" name="estado" value="{{ $state }}">
        @endif
        <x-ui.field name="comite" label="Comité" type="select" :options="[['value' => '', 'label' => 'Todos'], ...$committees]" :value="(string) ($filters['comite'] ?? '')" />
        <x-ui.field name="desde" label="Enviada desde" type="date" :value="$filters['desde']?->format('Y-m-d')" />
        <x-ui.field name="hasta" label="Enviada hasta" type="date" :value="$filters['hasta']?->format('Y-m-d')" />
    </x-ui.filter-bar>

    <x-ui.table caption="Evidencias del curso" :columns="$columns" :filterable="$evidences->isNotEmpty()" count-label="evidencias"
        filter-placeholder="Filtrar evidencias">
        <x-slot:toolbar>
            <x-ui.tabs label="Filtrar evidencias por estado" :items="$tabItems" />
        </x-slot:toolbar>
        @if ($evidences->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="list-checks" :title="$empty" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($evidences as $evidence)
            @php
                $href = route('profiles.view.evidence', [
                    'id_user' => $evidence->user->id,
                    'id_evidence' => $evidence->id,
                ]);
            @endphp
            <x-ui.table.row :href="$href" :search="implode(' ', [
                $evidence->title,
                $evidence->user->surname,
                $evidence->user->name,
                $evidence->committee?->name,
                $evidence->status_label,
            ])">
                <x-ui.table.cell label="Evidencia" primary>
                    <a href="{{ $href }}">{{ $evidence->title }}</a>
                </x-ui.table.cell>
                <x-ui.table.cell label="Autor">
                    <x-ui.person :name="$evidence->user->name" :surname="$evidence->user->surname" size="sm" reverse :href="route('profiles.view', ['id' => $evidence->user->id])" />
                </x-ui.table.cell>
                <x-ui.table.cell label="Comité" hide>
                    @if ($evidence->committee)
                        <x-ui.committee-chip :committee="$evidence->committee" :href="route($prefix . '.committee.view', ['id' => $evidence->committee->id])" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Creada" meta hide><x-ui.time :value="$evidence->created_at" inline
                        short /></x-ui.table.cell>
                <x-ui.table.cell label="Estado" end>
                    <x-ui.evidence-status :status="$evidence->status" :reason="$evidence->reasonRejection?->reason" size="sm" />
                    @if ($evidence->status === 'PENDING')
                        @php($waited = (int) $evidence->updated_at->diffInDays(now()))
                        <span
                            @class([
                                'mt-0.5 block text-sm',
                                'font-semibold text-rejected-ink' => $waited >= $lateDays,
                                'text-ink-muted' => $waited < $lateDays,
                            ])>{{ $waited === 0 ? 'en cola desde hoy' : 'en cola ' . $waited . ($waited === 1 ? ' día' : ' días') }}</span>
                    @endif
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($evidences->isNotEmpty())
            <x-slot:footer>
                <span>{{ $evidences->count() }} {{ $evidences->count() === 1 ? 'evidencia' : 'evidencias' }} ·
                    <x-ui.hours :value="$evidences->sum('hours')" size="sm" class="text-ink" /></span>
            </x-slot:footer>
        @endif
    </x-ui.table>
</x-layout.app>
