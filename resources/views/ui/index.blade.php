@php
    // Closed list of sections in their final order, grouped as the sidebar shows them; a section renders once its
    // file exists. The layout is the real app shell with demo data, so the catalogue is also its first consumer.
    $groups = [
        [
            'label' => 'Fundamentos',
            'icon' => 'palette',
            'mobile' => true,
            'sections' => [
                'tokens' => 'Tokens',
                'brand' => 'Marca',
                'icons' => 'Iconos',
                'accessibility' => 'Accesibilidad',
                'csp' => 'Sonda CSP',
            ],
        ],
        [
            'label' => 'Primitivas',
            'icon' => 'shapes',
            'mobile' => true,
            'sections' => [
                'button' => 'Botones',
                'badge' => 'Etiquetas',
                'avatar' => 'Personas',
                'time' => 'Fechas y horas',
                'meter' => 'Medidores',
                'card' => 'Tarjetas',
                'empty' => 'Estados vacíos',
            ],
        ],
        [
            'label' => 'Shell',
            'icon' => 'panels-top-left',
            'mobile' => true,
            'sections' => [
                'layout' => 'Layout',
                'nav' => 'Navegación',
                'page-header' => 'Cabecera de página',
                'footer' => 'Pie',
                'deadline' => 'Plazos',
                'dialog' => 'Diálogos',
                'menu' => 'Menús',
                'segmented' => 'Control segmentado',
                'notices' => 'Avisos',
                'bell' => 'Campana',
                'search' => 'Buscador',
                'count-chip' => 'Cifras',
            ],
        ],
        [
            'label' => 'Datos',
            'icon' => 'table',
            'mobile' => true,
            'sections' => [
                'table' => 'Tablas',
                'tabs' => 'Pestañas',
                'pagination' => 'Paginación',
                'stat' => 'Estadísticas',
                'bar-list' => 'Listas de barras',
                'dl' => 'Listas de definiciones',
                'timeline' => 'Recorridos e historial',
                'evidence-status' => 'Estados',
                'committee-chip' => 'Comités',
                'file-list' => 'Archivos',
                'notification-list' => 'Bandeja de avisos',
                'message' => 'Mensajes',
            ],
        ],
        [
            'label' => 'Formularios',
            'icon' => 'text-cursor-input',
            'mobile' => true,
            'sections' => [
                'form' => 'Formularios',
                'field' => 'Campos',
                'choice' => 'Casillas y conmutadores',
                'checkbox-group' => 'Grupos de casillas',
                'upload' => 'Subida de archivos',
                'picker' => 'Selector de personas',
                'icon-picker' => 'Selector de iconos',
                'filter-bar' => 'Filtros',
            ],
        ],
        [
            'label' => 'Detalle',
            'icon' => 'file-text',
            'mobile' => false,
            'sections' => [
                'alert' => 'Alertas',
                'disclosure' => 'Desplegables',
                'rich-text' => 'Texto enriquecido',
                'agenda-editor' => 'Orden del día',
                'minutes-editor' => 'Editor de actas',
            ],
        ],
    ];
    $sections = array_merge(...array_column($groups, 'sections'));
    $present = array_values(
        array_filter(array_keys($sections), fn(string $name): bool => view()->exists('ui.sections.' . $name)),
    );
    $pages = [
        ['label' => 'Shell', 'page' => 'shell', 'icon' => 'app-window'],
        ['label' => 'Acceso', 'page' => 'public', 'icon' => 'log-in'],
        ['label' => 'Detalle de evidencia', 'page' => 'detail', 'icon' => 'file-search'],
        ['label' => 'Acta en PDF', 'page' => 'pdf', 'icon' => 'printer'],
    ];
    $here = route('ui.index');
    $nav = [
        [
            'label' => 'Catálogo',
            'items' => array_values(
                array_map(
                    fn(array $group): array => [
                        'label' => $group['label'],
                        'href' => $here . '#ui-' . array_key_first($group['sections']),
                        'icon' => $group['icon'],
                        'mobile' => $group['mobile'],
                    ],
                    array_filter(
                        $groups,
                        fn(array $group): bool => in_array(array_key_first($group['sections']), $present, true),
                    ),
                ),
            ),
        ],
        [
            'label' => 'Páginas',
            'items' => array_values(
                array_map(
                    fn(array $page): array => [
                        'label' => $page['label'],
                        'href' => route('ui.page', $page['page']),
                        'icon' => $page['icon'],
                    ],
                    array_filter($pages, fn(array $page): bool => view()->exists('ui.pages.' . $page['page'])),
                ),
            ),
        ],
    ];
    $deadline = [
        'label' => 'Subida de evidencias',
        'opensAt' => now()->subDays(30),
        'closesAt' => now()->addDays(14),
        'href' => $here . '#ui-deadline',
    ];
    $user = [
        'name' => 'Juan',
        'surname' => 'Pérez',
        'roles' => ['Estudiante', 'Coordinador', 'Presidente'],
        'profileHref' => $here . '#ui-avatar',
        'logoutAction' => $here,
    ];
    // The layout renders the one stack of notices of the page; the notices section must not add another
    $hasGlobalNotices = true;
@endphp
<x-layout.app title="Sistema de diseño" :nav="$nav" :breadcrumbs="[['label' => 'Inicio', 'href' => $here], ['label' => 'Sistema de diseño']]" :deadline="$deadline" :user="$user"
    :notifications="['count' => 3, 'href' => $here . '#ui-notification-list']" :search="['action' => $here, 'placeholder' => 'Buscar componentes']" subtitle="Sistema de diseño" :version="evidentia_version() ?? '4.0.0'">
    <x-ui.page-header title="Sistema de diseño" lede="Tokens, componentes y layouts de Evidentia 4.">
        <x-slot:eyebrow>
            <x-ui.badge variant="chip" mono>{{ count($present) }} secciones</x-ui.badge>
            <x-ui.badge variant="chip" mono>{{ count($nav[1]['items']) }} páginas</x-ui.badge>
        </x-slot:eyebrow>
    </x-ui.page-header>

    <nav aria-label="Contenido" class="glass rounded-xl p-3 md:p-4">
        <ul class="flex flex-wrap gap-1">
            @foreach ($present as $name)
                <li>
                    <a href="#ui-{{ $name }}"
                        class="inline-flex rounded-full px-3 py-1.5 text-sm font-medium text-ink-muted no-underline transition hover:bg-glass-2 hover:text-ink pointer-coarse:min-h-11 pointer-coarse:items-center">{{ $sections[$name] }}</a>
                </li>
            @endforeach
        </ul>
    </nav>

    @foreach ($present as $name)
        @include('ui.sections.' . $name)
    @endforeach
</x-layout.app>
