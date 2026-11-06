{{-- Sections of the meeting management of a secretary, as URL tabs under the page header --}}
@php
    $route = (string) Route::currentRouteName();
    $sections = [
        [
            'label' => 'Resumen',
            'href' => route('secretary.meeting.manage'),
            'current' => $route === 'secretary.meeting.manage',
        ],
        [
            'label' => 'Convocatorias',
            'href' => route('secretary.meeting.manage.request.list'),
            'current' => str_starts_with($route, 'secretary.meeting.manage.request.'),
        ],
        [
            'label' => 'Hojas de firmas',
            'href' => route('secretary.meeting.manage.signaturesheet.list'),
            'current' => str_starts_with($route, 'secretary.meeting.manage.signaturesheet.'),
        ],
        [
            'label' => 'Actas',
            'href' => route('secretary.meeting.manage.minutes.list'),
            'current' => str_starts_with($route, 'secretary.meeting.manage.minutes.'),
        ],
    ];
@endphp
<x-ui.tabs label="Secciones de las reuniones" :items="$sections" size="md" class="min-w-0" />
