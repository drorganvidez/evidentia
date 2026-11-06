{{-- New minutes, step 1: the meeting call whose data and agenda fill the minutes --}}
@php
    $requestOptions = [
        ['value' => '', 'label' => 'Sin convocatoria'],
        ...$meeting_requests
            ->map(fn($meeting_request) => ['value' => $meeting_request->id, 'label' => $meeting_request->title])
            ->values()
            ->all(),
    ];
@endphp
<x-layout.app title="Nueva acta" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
    ['label' => 'Actas', 'href' => route('secretary.meeting.manage.minutes.list')],
    ['label' => 'Nueva'],
]">
    <x-ui.page-header title="Nueva acta" />

    @include('meeting.partials.tabs')

    @include('meeting.partials.minutes-steps', ['step' => 1])

    <x-ui.card>
        <x-ui.form :action="route('secretary.meeting.manage.minutes.create.step1_p')" id="minutes-step-form" :fields="['meeting_request' => 'Convocatoria']">
            <x-ui.field name="meeting_request" label="Convocatoria" type="select" :options="$requestOptions" />

            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="arrow-right" icon-end>Siguiente</x-ui.button>
                <x-ui.button variant="ghost" :href="route('secretary.meeting.manage.minutes.list')">Cancelar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.app>
