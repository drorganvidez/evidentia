{{-- Edit a signature sheet: its title and, while it has none, the meeting call it belongs to --}}
@php
    $requestOptions = [
        ['value' => '', 'label' => 'Sin cambios'],
        ...$available_meeting_requests
            ->map(fn($meeting_request) => ['value' => $meeting_request->id, 'label' => $meeting_request->title])
            ->values()
            ->all(),
    ];
@endphp
<x-layout.app title="Editar hoja de firmas" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
    ['label' => 'Hojas de firmas', 'href' => route('secretary.meeting.manage.signaturesheet.list')],
    [
        'label' => $signature_sheet->title,
        'href' => route('secretary.meeting.manage.signaturesheet.view', ['id' => $signature_sheet->id]),
    ],
    ['label' => 'Editar'],
]">
    <x-ui.page-header :title="$signature_sheet->title">
        <x-slot:actions>
            @include('meeting.partials.remove-sheet', [
                'signature_sheet' => $signature_sheet,
                'dialogId' => 'remove-sheet',
            ])
        </x-slot:actions>
    </x-ui.page-header>

    @include('meeting.partials.tabs')

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <x-ui.card>
            <x-ui.form :action="route('secretary.meeting.manage.signaturesheet.save')" id="signature-sheet-form" :fields="['title' => 'Título', 'meeting_request_id' => 'Convocatoria']">
                <input type="hidden" name="_id" value="{{ $signature_sheet->id }}">
                <x-ui.field name="title" label="Título" :value="$signature_sheet->title" required />
                <x-ui.field name="meeting_request_id" label="Convocatoria" type="select" :options="$requestOptions" />

                <x-slot:actions>
                    <x-ui.button type="submit" variant="primary" icon="file-signature">Guardar hoja de
                        firmas</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('secretary.meeting.manage.signaturesheet.view', ['id' => $signature_sheet->id])">Cancelar</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </x-ui.card>

        <aside aria-label="Convocatoria actual" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            @include('meeting.partials.sheet-request')
        </aside>
    </div>
</x-layout.app>
