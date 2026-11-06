{{-- The signature sheets of every committee for the lecturer and the president, newest first, with who signed. Read only. --}}
@php
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $columns = [
        ['label' => 'Hoja de firmas', 'key' => 'title'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Convocatoria', 'key' => 'request', 'hide' => true],
        ['label' => 'Firmas', 'key' => 'signatures', 'align' => 'end'],
        ['label' => 'Creada', 'key' => 'created_at', 'hide' => true, 'sorted' => 'desc'],
    ];
    $names = fn($users) => $users
        ->sortBy(fn($user) => mb_strtolower($user->surname . ' ' . $user->name))
        ->map(fn($user) => trim($user->surname . ', ' . $user->name, ', '))
        ->implode('; ');
@endphp
<x-layout.app title="Hojas de firmas" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route($prefix . '.meeting.list')],
    ['label' => 'Hojas de firmas'],
]">
    <x-ui.page-header title="Hojas de firmas" />

    @include('manage.partials.meeting-tabs')

    <x-ui.table caption="Hojas de firmas de todos los comités" :columns="$columns" sortable :filterable="$signature_sheets->isNotEmpty()"
        count-label="hojas" filter-placeholder="Filtrar hojas de firmas">
        @if ($signature_sheets->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="file-signature" title="No hay hojas de firmas" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($signature_sheets as $signature_sheet)
            @php
                $signers = $names($signature_sheet->users);
                $committee = $signature_sheet->secretary?->committee;
                $request = $signature_sheet->meetingRequest;
                $detail = $request ? route('activity.request', ['id' => $request->id]) : null;
            @endphp
            <x-ui.table.row :href="$detail" :search="implode(' ', [$signature_sheet->title, $committee?->name, $request?->title, $signers])">
                <x-ui.table.cell label="Hoja de firmas" primary>
                    @if ($detail)
                        <a href="{{ $detail }}">{{ $signature_sheet->title }}</a>
                    @else
                        <span class="font-semibold tracking-tight">{{ $signature_sheet->title }}</span>
                    @endif
                    @if ($signers !== '')
                        <span class="mt-0.5 block max-w-[48ch] text-sm text-ink-muted">{{ $signers }}</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Comité">
                    @if ($committee)
                        <x-ui.committee-chip :committee="$committee" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Convocatoria" :sort-value="$request?->title ?? ''"
                    hide>{{ $request?->title ?? 'Sin convocatoria' }}</x-ui.table.cell>
                <x-ui.table.cell label="Firmas" numeric meta>{{ $signature_sheet->users->count() }}</x-ui.table.cell>
                <x-ui.table.cell label="Creada" meta hide>
                    <x-ui.time :value="$signature_sheet->created_at" inline short />
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
    </x-ui.table>
</x-layout.app>
