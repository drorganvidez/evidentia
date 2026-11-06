{{-- Edit minutes: the meeting data, the attendees and the points; saving rebuilds the PDF --}}
@php
    $meeting = $meeting_minutes->meeting;
@endphp
<x-layout.app title="Editar acta" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
    ['label' => 'Actas', 'href' => route('secretary.meeting.manage.minutes.list')],
    ['label' => 'Editar'],
]">
    <x-ui.page-header :title="$meeting->title">
        <x-slot:actions>
            <x-ui.button icon="file-text" :href="route('download.minutes', ['id' => $meeting_minutes->id])">Descargar
                PDF</x-ui.button>
            @include('meeting.partials.remove-minutes', [
                'meeting_minutes' => $meeting_minutes,
                'dialogId' => 'remove-minutes',
            ])
        </x-slot:actions>
    </x-ui.page-header>

    @include('meeting.partials.tabs')

    <x-ui.form :action="route('secretary.meeting.manage.minutes.save')" id="minutes-form" :fields="[
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
        <input type="hidden" name="_id" value="{{ $meeting_minutes->id }}">

        @include('meeting.partials.minutes-fields', [
            'source' => $meeting,
            'hours' => $meeting->hours,
            'selected' => $meeting->users->modelKeys(),
            'points' => $points,
        ])

        <x-slot:actions>
            <x-ui.button type="submit" variant="primary" icon="scroll-text">Guardar acta</x-ui.button>
            <x-ui.button variant="ghost" :href="route('secretary.meeting.manage.minutes.list')">Cancelar</x-ui.button>
        </x-slot:actions>
    </x-ui.form>
</x-layout.app>
