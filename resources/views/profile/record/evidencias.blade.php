{{-- Section of the record: the last version of every evidence the person sent, with its history, ordered in the browser --}}
@php
    $columns = [
        ['label' => 'Evidencia', 'key' => 'title'],
        ['label' => 'Comité', 'key' => 'committee', 'hide' => true],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Versión', 'key' => 'version', 'align' => 'end'],
        ['label' => 'Estado', 'key' => 'status'],
        ['label' => 'Actualizada', 'key' => 'updated', 'sorted' => 'desc'],
        ['label' => 'Historial', 'key' => 'history', 'srOnly' => true],
    ];
    $sum = $totals['evidences'];
@endphp
<x-ui.table caption="Evidencias enviadas" :columns="$columns" show-caption sortable>
    @foreach ($evidences as $evidence)
        @php
            $href = route('profiles.view.evidence', ['id_user' => $user->id, 'id_evidence' => $evidence->id]);
        @endphp
        <x-ui.table.row :href="$href">
            <x-ui.table.cell label="Evidencia" primary><a
                    href="{{ $href }}">{{ $evidence->title }}</a></x-ui.table.cell>
            <x-ui.table.cell label="Comité" hide>
                @if ($evidence->committee)
                    <x-ui.committee-chip :committee="$evidence->committee" :href="route($prefix . '.committee.view', ['id' => $evidence->committee->id])" />
                @endif
            </x-ui.table.cell>
            <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence->hours" /></x-ui.table.cell>
            <x-ui.table.cell numeric meta :sort-value="$evidence->version"><span class="md:hidden">Versión
                </span>{{ $evidence->version }}</x-ui.table.cell>
            <x-ui.table.cell label="Estado" end><x-ui.evidence-status :status="$evidence->status" :reason="$evidence->reasonRejection?->reason"
                    size="sm" /></x-ui.table.cell>
            <x-ui.table.cell label="Actualizada" meta><x-ui.time :value="$evidence->updated_at" inline short /></x-ui.table.cell>
            <x-ui.table.cell>
                <x-ui.button :href="route('activity.evidence', ['id' => $evidence->id])" icon="history" size="sm" variant="ghost"
                    :aria-label="'Historial de ' . $evidence->title">Historial</x-ui.button>
            </x-ui.table.cell>
        </x-ui.table.row>
    @endforeach
    @if ($evidences->isEmpty())
        <x-slot:empty>
            <x-ui.empty icon="file-text" title="No ha enviado evidencias" size="sm" />
        </x-slot:empty>
    @else
        <x-slot:footer>
            <p>{{ $plural($evidences->count(), 'enviada', 'enviadas') }} ·
                {{ $plural($sum['accepted'], 'aceptada', 'aceptadas') }} ·
                {{ $plural($sum['pending'], 'pendiente', 'pendientes') }} ·
                {{ $plural($sum['rejected'], 'rechazada', 'rechazadas') }}</p>
            <p class="flex flex-wrap items-baseline gap-x-1.5"><x-ui.hours :value="$sum['acceptedHours']" size="sm" />
                <span>aceptadas de</span> <x-ui.hours :value="$sum['hours']" size="sm" />
            </p>
        </x-slot:footer>
    @endif
</x-ui.table>
