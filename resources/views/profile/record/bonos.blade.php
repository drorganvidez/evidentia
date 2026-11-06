{{-- Section of the record: the bonuses the person received, with the committee that granted them --}}
@php
    $columns = [
        ['label' => 'Motivo', 'key' => 'reason'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Fecha', 'key' => 'created_at'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
    ];
@endphp
<x-ui.table caption="Bonos" :columns="$columns" show-caption>
    @foreach ($bonuses as $bonus)
        <x-ui.table.row>
            <x-ui.table.cell label="Motivo" primary><span
                    class="font-semibold">{{ $bonus->reason }}</span></x-ui.table.cell>
            <x-ui.table.cell label="Comité">
                @if ($bonus->committee)
                    <x-ui.committee-chip :committee="$bonus->committee" :href="route($prefix . '.committee.view', ['id' => $bonus->committee->id])" />
                @endif
            </x-ui.table.cell>
            <x-ui.table.cell label="Fecha" meta><x-ui.time :value="$bonus->created_at" format="absolute"
                    pattern="d/m/Y" /></x-ui.table.cell>
            <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$bonus->hours" /></x-ui.table.cell>
        </x-ui.table.row>
    @endforeach
    @if ($bonuses->isEmpty())
        <x-slot:empty>
            <x-ui.empty icon="gift" title="Sin bonos" size="sm" />
        </x-slot:empty>
    @else
        <x-slot:footer>
            <p>{{ $plural($bonuses->count(), 'bono', 'bonos') }}</p>
            <p><x-ui.hours :value="$totals['bonuses']" size="sm" /> computadas</p>
        </x-slot:footer>
    @endif
</x-ui.table>
