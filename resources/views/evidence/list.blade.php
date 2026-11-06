{{-- The latest version of every evidence of the signed-in person, newest first, with what they can still do to each --}}
@php
    $closesAt = \Config::upload_evidences_timestamp();
    // Open as the routes read it (CheckUploadEvidences): until the date passes, also when there is none
    $open = !now()->gt($closesAt);
    $columns = [
        ['label' => 'Evidencia', 'key' => 'title'],
        ['label' => 'Comité', 'key' => 'committee', 'hide' => true],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Estado', 'key' => 'status'],
        ['label' => 'Creada', 'key' => 'created'],
        ['label' => 'Acciones', 'srOnly' => true],
    ];
@endphp
<x-layout.app title="Mis evidencias" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Mis evidencias']]">
    <x-ui.page-header title="Mis evidencias">
        @if ($open && $closesAt !== null)
            <x-slot:meta>
                <x-ui.deadline label="Subida de evidencias" variant="inline" verb="para subir evidencias"
                    :closes-at="$closesAt" />
            </x-slot:meta>
        @endif
        @if ($open)
            <x-slot:actions>
                <x-ui.button variant="primary" size="lg" icon="plus" :href="route('evidence.create')" block class="md:w-auto">Nueva
                    evidencia</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.table caption="Mis evidencias" :columns="$columns" :filterable="$evidences->isNotEmpty()" count-label="evidencias"
        filter-placeholder="Filtrar evidencias">
        @foreach ($evidences as $evidence)
            @php
                $href = route('evidence.view', ['id' => $evidence->id]);
            @endphp
            <x-ui.table.row :href="$href">
                <x-ui.table.cell label="Evidencia" primary>
                    <a href="{{ $href }}">{{ $evidence->title }}</a>
                </x-ui.table.cell>
                <x-ui.table.cell label="Comité" hide>
                    <x-ui.committee-chip :committee="$evidence->committee" />
                </x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Estado" end>
                    <x-ui.evidence-status :status="$evidence->status" :reason="$evidence->reasonRejection?->reason" size="sm" />
                </x-ui.table.cell>
                <x-ui.table.cell label="Creada" meta><x-ui.time :value="$evidence->created_at" inline short /></x-ui.table.cell>
                <x-ui.table.cell actions>
                    <div class="flex items-center justify-end gap-1">
                        <x-ui.button icon-only icon="eye" :label="'Abrir ' . $evidence->title" :href="$href" variant="ghost"
                            size="sm" dim />
                        @include('evidence.partials.actions', ['evidence' => $evidence, 'compact' => true])
                    </div>
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($evidences->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="file-text" title="Aún no hay evidencias" size="sm" :level="2">
                    @if ($open)
                        <x-slot:actions>
                            <x-ui.button variant="primary" icon="plus" :href="route('evidence.create')">Nueva
                                evidencia</x-ui.button>
                        </x-slot:actions>
                    @endif
                </x-ui.empty>
            </x-slot:empty>
        @else
            <x-slot:footer>
                <span>{{ $evidences->count() }} {{ $evidences->count() === 1 ? 'evidencia' : 'evidencias' }} ·
                    <x-ui.hours :value="$evidences->sum('hours')" size="sm" class="text-ink" /> declaradas</span>
            </x-slot:footer>
        @endif
    </x-ui.table>
</x-layout.app>
