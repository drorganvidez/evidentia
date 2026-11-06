{{-- Section of the record: the meetings the person attended, with their minutes, ordered in the browser --}}
@php
    $columns = [
        ['label' => 'Reunión', 'key' => 'title'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Fecha', 'key' => 'datetime', 'sorted' => 'desc'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
    ];
@endphp
<x-ui.table caption="Reuniones" :columns="$columns" show-caption sortable>
    @foreach ($meetings as $meeting)
        @php
            $href = $meeting->meetingMinutes ? route('activity.minutes', ['id' => $meeting->meetingMinutes->id]) : null;
        @endphp
        <x-ui.table.row :href="$href">
            <x-ui.table.cell label="Reunión" primary>
                @if ($href)
                    <a href="{{ $href }}">{{ $meeting->title }}</a>
                @else
                    <span class="font-semibold">{{ $meeting->title }}</span>
                @endif
            </x-ui.table.cell>
            <x-ui.table.cell label="Comité">
                @if ($meeting->committee)
                    <x-ui.committee-chip :committee="$meeting->committee" :href="route($prefix . '.committee.view', ['id' => $meeting->committee->id])" />
                @endif
            </x-ui.table.cell>
            <x-ui.table.cell label="Fecha" meta><x-ui.time :value="$meeting->datetime" format="absolute"
                    pattern="d/m/Y H:i" /></x-ui.table.cell>
            <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$meeting->hours" /></x-ui.table.cell>
        </x-ui.table.row>
    @endforeach
    @if ($meetings->isEmpty())
        <x-slot:empty>
            <x-ui.empty icon="handshake" title="No consta en ninguna reunión" size="sm" />
        </x-slot:empty>
    @else
        <x-slot:footer>
            <p>{{ $plural($meetings->count(), 'reunión', 'reuniones') }}</p>
            <p class="flex flex-wrap items-baseline gap-x-1.5"><x-ui.hours :value="$totals['meetings']" size="sm" />
                <span>computadas</span>
            </p>
        </x-slot:footer>
    @endif
</x-ui.table>
