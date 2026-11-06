{{-- Sections of the meetings of every committee for the management, as URL tabs under the page header --}}
@php
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $route = (string) Route::currentRouteName();
    $sections = array_map(
        fn(array $section): array => [
            'label' => $section[0],
            'href' => route($prefix . '.' . $section[1]),
            'current' => $route === $prefix . '.' . $section[1],
        ],
        [
            ['Reuniones', 'meeting.list'],
            ['Convocatorias', 'meeting.request.list'],
            ['Hojas de firmas', 'meeting.signaturesheet.list'],
            ['Listas', 'meeting.defaultlist.list'],
        ],
    );
@endphp
<x-ui.tabs label="Secciones de las reuniones" :items="$sections" size="md" class="min-w-0" />
