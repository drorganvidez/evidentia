{{--
    Section of the record: the sheets the person signed and the meeting requests of the committees they belong to (by a
    default list or because they run it), with how the person shows in each one; both order in the browser, the
    attendance from the strongest record to none
--}}
@php
    $sheetColumns = [
        ['label' => 'Hoja de firmas', 'key' => 'title'],
        ['label' => 'Convocatoria', 'key' => 'request'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Firmada', 'key' => 'signed_at', 'sorted' => 'desc'],
    ];
    $requestColumns = [
        ['label' => 'Convocatoria', 'key' => 'title'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Fecha', 'key' => 'datetime', 'sorted' => 'desc'],
        ['label' => 'Asistencia', 'key' => 'state'],
        ['label' => 'Acta', 'key' => 'minutes', 'srOnly' => true],
    ];
    $attendanceOrder = ['signed', 'minutes', 'upcoming', 'missing'];
    $recorded = $requests->whereIn('state', ['signed', 'minutes'])->count();
    $missing = $requests->where('state', 'missing')->count();
@endphp
<div class="grid grid-cols-[minmax(0,1fr)] gap-6">
    <x-ui.table caption="Hojas firmadas" :columns="$sheetColumns" show-caption sortable>
        @foreach ($signatures as $sheet)
            @php
                $request = $sheet->meetingRequest;
                $href = $request ? route('activity.request', ['id' => $request->id]) : null;
                $committee = $request?->committee ?? $sheet->secretary?->committee;
            @endphp
            <x-ui.table.row :href="$href">
                <x-ui.table.cell label="Hoja de firmas" primary>
                    @if ($href)
                        <a href="{{ $href }}">{{ $sheet->title }}</a>
                    @else
                        <span class="font-semibold">{{ $sheet->title }}</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Convocatoria">{{ $request?->title ?? '—' }}</x-ui.table.cell>
                <x-ui.table.cell label="Comité">
                    @if ($committee)
                        <x-ui.committee-chip :committee="$committee" :href="route($prefix . '.committee.view', ['id' => $committee->id])" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Firmada" meta><x-ui.time :value="$sheet->signed_at" inline short /></x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($signatures->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="file-signature" title="No ha firmado ninguna hoja" size="sm" />
            </x-slot:empty>
        @else
            <x-slot:footer>
                <p>{{ $plural($signatures->count(), 'firma', 'firmas') }}</p>
            </x-slot:footer>
        @endif
    </x-ui.table>

    <x-ui.table caption="Convocatorias de sus comités" :columns="$requestColumns" show-caption sortable>
        @foreach ($requests as $item)
            @php
                $request = $item['request'];
                $href = route('activity.request', ['id' => $request->id]);
                $minutes = $item['minutes'];
            @endphp
            <x-ui.table.row :href="$href">
                <x-ui.table.cell label="Convocatoria" primary><a
                        href="{{ $href }}">{{ $request->title }}</a></x-ui.table.cell>
                <x-ui.table.cell label="Comité">
                    @if ($request->committee)
                        <x-ui.committee-chip :committee="$request->committee" :href="route($prefix . '.committee.view', ['id' => $request->committee->id])" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Fecha" meta><x-ui.time :value="$request->datetime" format="absolute"
                        pattern="d/m/Y H:i" /></x-ui.table.cell>
                <x-ui.table.cell label="Asistencia" end :sort-value="array_search($item['state'], $attendanceOrder, true)"><x-ui.badge :tone="$item['tone']"
                        size="sm">{{ $item['label'] }}</x-ui.badge></x-ui.table.cell>
                <x-ui.table.cell>
                    @if ($minutes)
                        <x-ui.button :href="route('activity.minutes', ['id' => $minutes])" icon="file-text" size="sm" variant="ghost"
                            :aria-label="'Acta de ' . $request->title">Acta</x-ui.button>
                    @endif
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($requests->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="calendar" title="Sin convocatorias en sus comités" size="sm" />
            </x-slot:empty>
        @else
            <x-slot:footer>
                <p>{{ $plural($requests->count(), 'convocatoria', 'convocatorias') }} ·
                    {{ $plural($recorded, 'con constancia', 'con constancia') }} ·
                    {{ $plural($missing, 'sin constancia', 'sin constancia') }}</p>
            </x-slot:footer>
        @endif
    </x-ui.table>
</div>
