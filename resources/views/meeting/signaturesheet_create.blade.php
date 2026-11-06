{{-- New signature sheet, optionally tied to a meeting call that has none yet --}}
@php
    $requestOptions = [
        ['value' => '', 'label' => 'Sin convocatoria'],
        ...$available_meeting_requests
            ->map(fn($meeting_request) => ['value' => $meeting_request->id, 'label' => $meeting_request->title])
            ->values()
            ->all(),
    ];
@endphp
<x-layout.app title="Nueva hoja de firmas" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
    ['label' => 'Hojas de firmas', 'href' => route('secretary.meeting.manage.signaturesheet.list')],
    ['label' => 'Nueva'],
]">
    <x-ui.page-header title="Nueva hoja de firmas" />

    @include('meeting.partials.tabs')

    <x-ui.card>
        <x-ui.form :action="route('secretary.meeting.manage.signaturesheet.new')" id="signature-sheet-form" :fields="['title' => 'Título', 'meeting_request' => 'Convocatoria']">
            <x-ui.field name="title" label="Título" required />
            <x-ui.field name="meeting_request" label="Convocatoria" type="select" :options="$requestOptions" />

            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="file-signature">Crear hoja de firmas</x-ui.button>
                <x-ui.button variant="ghost" :href="route('secretary.meeting.manage.signaturesheet.list')">Cancelar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.app>
