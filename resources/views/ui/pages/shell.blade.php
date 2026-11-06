{{-- The app layout with demo data: a person with several roles, the breadcrumbs, a deadline, the inbox, the search and a dashboard --}}
@php
    $here = route('ui.page', 'shell');
    $nav = [
        [
            'label' => null,
            'items' => [
                [
                    'label' => 'Inicio',
                    'href' => $here,
                    'icon' => 'layout-dashboard',
                    'current' => true,
                    'mobile' => true,
                ],
                ['label' => 'Mi perfil', 'href' => $here . '#perfil', 'icon' => 'user-round'],
                [
                    'label' => 'Mis mensajes',
                    'href' => $here . '#mensajes',
                    'icon' => 'message-square',
                    'count' => 2,
                    'countLabel' => 'sin leer',
                ],
            ],
        ],
        [
            'label' => 'Mis cosas',
            'items' => [
                ['label' => 'Nueva evidencia', 'href' => $here . '#nueva', 'icon' => 'plus'],
                ['label' => 'Mis evidencias', 'href' => $here . '#evidencias', 'icon' => 'file-text', 'mobile' => true],
                ['label' => 'Mis reuniones', 'href' => $here . '#reuniones', 'icon' => 'handshake', 'mobile' => true],
                ['label' => 'Mis asistencias', 'href' => $here . '#asistencias', 'icon' => 'ticket', 'mobile' => true],
            ],
        ],
        [
            'label' => 'Comité',
            'items' => [
                [
                    'label' => 'Revisar evidencias',
                    'href' => $here . '#revisar',
                    'icon' => 'list-checks',
                    'count' => 6,
                    'countLabel' => 'pendientes',
                ],
            ],
        ],
        [
            'label' => 'Gestión',
            'items' => [
                ['label' => 'Configurar curso', 'href' => $here . '#configurar', 'icon' => 'settings'],
                ['label' => 'Alumnos', 'href' => $here . '#alumnos', 'icon' => 'user-cog'],
                ['label' => 'Evidencias', 'href' => $here . '#evidencias-gestion', 'icon' => 'list-checks'],
                ['label' => 'Reuniones', 'href' => $here . '#reuniones-gestion', 'icon' => 'handshake'],
                ['label' => 'Comités', 'href' => $here . '#comites', 'icon' => 'network'],
                ['label' => 'Integridad', 'href' => $here . '#integridad', 'icon' => 'shield-check'],
                ['label' => 'Exportar', 'href' => $here . '#exportar', 'icon' => 'download'],
            ],
        ],
    ];
    $mobileNav = [
        ['label' => 'Inicio', 'href' => $here, 'icon' => 'layout-dashboard', 'current' => true],
        ['label' => 'Evidencias', 'href' => $here . '#evidencias', 'icon' => 'file-text'],
        ['label' => 'Reuniones', 'href' => $here . '#reuniones', 'icon' => 'handshake'],
        ['label' => 'Asistencias', 'href' => $here . '#asistencias', 'icon' => 'ticket'],
        ['label' => 'Perfil', 'href' => $here . '#perfil', 'icon' => 'user-round'],
    ];
    $deadline = [
        'label' => 'Subida de evidencias',
        'opensAt' => now()->subDays(30),
        'closesAt' => now()->addDays(14),
        'href' => $here . '#evidencias',
    ];
    $user = [
        'name' => 'Juan',
        'surname' => 'Pérez',
        'roles' => ['Estudiante', 'Coordinador', 'Presidente'],
        'profileHref' => $here . '#perfil',
        'logoutAction' => route('ui.demo.submit'),
    ];
    $cards = [
        ['title' => 'Horas aceptadas', 'value' => 4.0, 'text' => 'de 8,25 h declaradas', 'tone' => 'accepted'],
        ['title' => 'Pendientes', 'value' => 3.75, 'text' => '2 evidencias en revisión', 'tone' => 'pending'],
        ['title' => 'Rechazadas', 'value' => 0.5, 'text' => '1 por corregir', 'tone' => 'rejected'],
    ];
@endphp
<x-layout.app title="Inicio" :nav="$nav" :mobile-nav="$mobileNav" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => $here],
    ['label' => 'Mis evidencias', 'href' => $here . '#evidencias'],
    ['label' => 'Todas'],
]" :deadline="$deadline" :user="$user"
    :aurora="$aurora ?? true" :notifications="['count' => 3, 'href' => $here . '#avisos']" :search="['action' => route('ui.index')]" subtitle="Jornadas InnoSoft 2026" version="4.0.0">
    <x-ui.page-header title="Hola, Juan"
        lede="Tienes dos evidencias pendientes de revisión y un plazo que cierra en dos semanas.">
        <x-slot:meta>
            <x-ui.deadline label="Subida de evidencias" variant="inline" verb="para subir evidencias" :closes-at="$deadline['closesAt']" />
        </x-slot:meta>
        <x-slot:actions>
            <x-ui.button variant="primary" size="lg" icon="plus" :href="$here . '#nueva'" block class="md:w-auto">Nueva
                evidencia</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-3">
        @foreach ($cards as $card)
            <x-ui.card :title="$card['title']" :level="2" :href="$here . '#evidencias'" arrow>
                <p class="flex flex-wrap items-baseline gap-x-3">
                    <x-ui.hours :value="$card['value']" size="xl" class="font-bold tracking-tighter" />
                </p>
                <p class="mt-1 inline-flex items-center gap-2 text-sm text-ink-muted">
                    <x-ui.dot :tone="$card['tone']" />
                    {{ $card['text'] }}
                </p>
            </x-ui.card>
        @endforeach
    </div>

    <x-ui.card title="Últimas evidencias" :level="2">
        <x-slot:extra>
            <x-ui.badge variant="count">6</x-ui.badge>
        </x-slot:extra>
        <ul class="divide-y divide-rule">
            @foreach ([['Montaje del photocall', 'pending', 'Pendiente de revisión', 1.75], ['Acta de la reunión 3', 'accepted', 'Aceptada', 2.0], ['Cartelería del vestíbulo', 'rejected', 'Rechazada', 0.5]] as [$name, $tone, $state, $hours])
                <li class="flex flex-wrap items-center gap-x-4 gap-y-2 py-3">
                    <span class="min-w-0 flex-1 font-semibold">{{ $name }}</span>
                    <x-ui.badge :tone="$tone" size="sm">{{ $state }}</x-ui.badge>
                    <x-ui.hours :value="$hours" size="sm" />
                </li>
            @endforeach
        </ul>
    </x-ui.card>
</x-layout.app>
