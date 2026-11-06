{{-- New minutes, step 3: the meeting data, the attendees and the points, filled from the call and the sheet chosen --}}
@php
    $agenda = $meeting_request?->diary?->diaryPoints?->map(fn($point) => ['title' => $point->point])->all() ?? [];
@endphp
<x-layout.app title="Nueva acta" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
    ['label' => 'Actas', 'href' => route('secretary.meeting.manage.minutes.list')],
    ['label' => 'Nueva'],
]">
    <x-ui.page-header title="Nueva acta" />

    @include('meeting.partials.tabs')

    @include('meeting.partials.minutes-steps', ['step' => 3])

    <x-ui.form :action="route('secretary.meeting.manage.minutes.create.step3_p')" id="minutes-form" :fields="[
        'title' => 'Título',
        'date' => 'Fecha',
        'time' => 'Hora',
        'place' => 'Lugar',
        'type' => 'Tipo',
        'modality' => 'Modalidad',
        'hours' => 'Horas',
        'minutes' => 'Minutos',
        'users' => 'Asistentes',
    ]">
        <input type="hidden" name="meeting_request" value="{{ $meeting_request->id ?? '' }}">

        @include('meeting.partials.minutes-fields', [
            'source' => $meeting_request,
            'hours' => null,
            'selected' => $signature_sheet?->users->modelKeys() ?? [],
            'points' => $agenda,
        ])

        <x-slot:actions>
            <x-ui.button type="submit" variant="primary" icon="scroll-text">Crear acta</x-ui.button>
            <x-ui.button variant="ghost" :href="route('secretary.meeting.manage.minutes.list')">Cancelar</x-ui.button>
        </x-slot:actions>
    </x-ui.form>
</x-layout.app>
