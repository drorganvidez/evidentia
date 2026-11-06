{{-- Every evidence sent in the course, for the lecturer and the president, with one tab per state --}}
@php
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $list = route($prefix . '.evidence.list');
    $tabs = [
        ['Todas', null, $counts->sum()],
        ['Pendientes', 'pendientes', $counts->get('PENDING', 0)],
        ['Aceptadas', 'aceptadas', $counts->get('ACCEPTED', 0)],
        ['Rechazadas', 'rechazadas', $counts->get('REJECTED', 0)],
    ];
    $empty = match ($state) {
        'pendientes' => 'No hay evidencias pendientes',
        'aceptadas' => 'No hay evidencias aceptadas',
        'rechazadas' => 'No hay evidencias rechazadas',
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

    <x-ui.table caption="Evidencias del curso" :columns="$columns" :filterable="$evidences->isNotEmpty()"
        count-label="evidencias" filter-placeholder="Filtrar evidencias">
        <x-slot:toolbar>
            <x-ui.tabs label="Filtrar evidencias por estado" :items="collect($tabs)
                ->map(
                    fn($tab) => [
                        'label' => $tab[0],
                        'href' => $tab[1] === null ? $list : $list . '?estado=' . $tab[1],
                        'count' => $tab[2],
                        'current' => $state === $tab[1],
                    ],
                )
                ->all()" />
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
                    <x-ui.person :name="$evidence->user->name" :surname="$evidence->user->surname" size="sm" reverse
                        :href="route('profiles.view', ['id' => $evidence->user->id])" />
                </x-ui.table.cell>
                <x-ui.table.cell label="Comité" hide>
                    @if ($evidence->committee)
                        <x-ui.committee-chip :committee="$evidence->committee" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Creada" meta hide><x-ui.time :value="$evidence->created_at" inline
                        short /></x-ui.table.cell>
                <x-ui.table.cell label="Estado" end>
                    <x-ui.evidence-status :status="$evidence->status" :reason="$evidence->reasonRejection?->reason" size="sm" />
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
