{{-- Signature sheets of the secretary: each row opens the sheet with its signatures, its link and its actions --}}
@php
    $columns = [
        ['label' => 'Hoja de firmas', 'key' => 'title'],
        ['label' => 'Convocatoria', 'key' => 'request', 'hide' => true],
        ['label' => 'Firmas', 'key' => 'signatures', 'align' => 'end'],
        ['label' => 'Modificada', 'key' => 'updated_at', 'hide' => true],
        ['label' => 'Acciones', 'srOnly' => true],
    ];
@endphp
<x-layout.app title="Hojas de firmas" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
    ['label' => 'Hojas de firmas'],
]">
    <x-ui.page-header title="Hojas de firmas">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="plus" :href="route('secretary.meeting.manage.signaturesheet.create')">Nueva hoja de
                firmas</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @include('meeting.partials.tabs')

    <x-ui.table caption="Hojas de firmas" :columns="$columns" :filterable="$signature_sheets->isNotEmpty()" count-label="hojas"
        filter-placeholder="Filtrar hojas de firmas">
        @forelse ($signature_sheets as $signature_sheet)
            @php
                $view = route('secretary.meeting.manage.signaturesheet.view', ['id' => $signature_sheet->id]);
                $signUrl = url('/sign/' . $signature_sheet->random_identifier);
            @endphp
            <x-ui.table.row :href="$view">
                <x-ui.table.cell label="Hoja de firmas" primary>
                    <a href="{{ $view }}">{{ $signature_sheet->title }}</a>
                    <span class="mt-0.5 block font-mono text-sm text-ink-muted">{{ $signUrl }}</span>
                </x-ui.table.cell>
                <x-ui.table.cell label="Convocatoria" hide>
                    {{ $signature_sheet->meetingRequest->title ?? 'Sin convocatoria' }}
                </x-ui.table.cell>
                <x-ui.table.cell label="Firmas" numeric meta>{{ $signature_sheet->users->count() }}</x-ui.table.cell>
                <x-ui.table.cell label="Modificada" meta hide>
                    <x-ui.time :value="$signature_sheet->updated_at" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell actions>
                    <span class="flex justify-end gap-0.5">
                        <x-ui.button icon-only icon="copy" variant="ghost" size="sm" :label="'Copiar el enlace de ' . $signature_sheet->title"
                            :copy="$signUrl" />
                        <x-ui.button icon-only icon="pen-line" variant="ghost" size="sm" :label="'Editar ' . $signature_sheet->title"
                            :href="route('secretary.meeting.manage.signaturesheet.edit', [
                                'id' => $signature_sheet->id,
                            ])" />
                        @include('meeting.partials.remove-sheet', [
                            'signature_sheet' => $signature_sheet,
                            'dialogId' => 'remove-sheet-' . $signature_sheet->id,
                            'iconOnly' => true,
                        ])
                    </span>
                </x-ui.table.cell>
            </x-ui.table.row>
        @empty
            <x-slot:empty>
                <x-ui.empty icon="file-signature" title="Sin hojas de firmas" size="sm" :level="2">
                    <x-slot:actions>
                        <x-ui.button variant="primary" icon="plus" :href="route('secretary.meeting.manage.signaturesheet.create')">Nueva
                            hoja de firmas</x-ui.button>
                    </x-slot:actions>
                </x-ui.empty>
            </x-slot:empty>
        @endforelse
    </x-ui.table>
</x-layout.app>
