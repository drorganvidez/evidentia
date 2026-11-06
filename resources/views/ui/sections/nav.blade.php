{{-- Navigation: the sidebar list with its groups, the rail of the tablet, the bottom bar of the phone and the breadcrumbs --}}
@php
    $here = route('ui.index') . '#ui-nav';
    $main = [
        ['label' => 'Inicio', 'href' => $here, 'icon' => 'layout-dashboard'],
        ['label' => 'Mis evidencias', 'href' => $here, 'icon' => 'file-text', 'current' => true, 'count' => 6],
        ['label' => 'Mis reuniones', 'href' => $here, 'icon' => 'handshake'],
        ['label' => 'Mis asistencias', 'href' => $here, 'icon' => 'ticket'],
        ['label' => 'Mi perfil', 'href' => $here, 'icon' => 'user-round'],
    ];
    $management = [
        ['label' => 'Configurar curso', 'href' => $here, 'icon' => 'settings'],
        ['label' => 'Alumnos', 'href' => $here, 'icon' => 'user-cog'],
        [
            'label' => 'Evidencias',
            'href' => $here,
            'icon' => 'list-checks',
            'count' => 12,
            'countLabel' => 'pendientes',
        ],
        ['label' => 'Comités', 'href' => $here, 'icon' => 'network'],
    ];
    $mobile = [
        ['label' => 'Inicio', 'href' => $here, 'icon' => 'layout-dashboard'],
        ['label' => 'Evidencias', 'href' => $here, 'icon' => 'file-text', 'current' => true],
        ['label' => 'Reuniones', 'href' => $here, 'icon' => 'handshake'],
        ['label' => 'Asistencias', 'href' => $here, 'icon' => 'ticket'],
        ['label' => 'Perfil', 'href' => $here, 'icon' => 'user-round'],
    ];
    $crumbs = [
        ['label' => 'Inicio', 'href' => $here],
        ['label' => 'Mis evidencias', 'href' => $here],
        ['label' => 'Montaje del photocall con un título largo que se trunca'],
    ];
@endphp
<section id="ui-nav" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-nav-title">
    <h2 id="ui-nav-title" class="text-2xl font-bold tracking-tight">Navegación</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-[17rem_5rem_minmax(0,1fr)]">
        <div class="glass-2 grid content-start gap-3 rounded-lg p-4" data-demo="sidebar">
            <span class="font-mono text-2xs text-ink-muted">sidebar · heading</span>
            {{-- Without rail the list keeps its full form at every width --}}
            <x-ui.nav :items="$main" :rail="false" label="Navegación principal (demo)" />
            <x-ui.nav :items="$management" :rail="false" heading="Gestión" label="Gestión (demo)" />
        </div>

        <div class="glass-2 grid max-w-20 content-start gap-3 rounded-lg px-2 py-4" data-demo="rail">
            <span class="justify-self-center font-mono text-2xs text-ink-muted">raíl</span>
            <x-ui.nav variant="rail" :items="$main" label="Navegación principal (raíl)" />
        </div>

        <div class="grid content-start gap-3" data-demo="bar">
            <span class="font-mono text-2xs text-ink-muted">variant="bar" · marco de 390 px</span>
            <div
                class="mx-auto w-full max-w-[390px] rounded-xl border border-rule bg-canvas p-3 [&_nav]:static [&_nav]:block">
                <x-ui.nav variant="bar" :items="$mobile" label="Navegación principal (barra)" />
            </div>
        </div>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Migas de pan</h3>
        <div class="glass-2 grid grid-cols-[minmax(0,1fr)] gap-3 rounded-lg p-4" data-demo="breadcrumbs">
            <span class="font-mono text-2xs text-ink-muted">la última es la página actual; las anteriores se ocultan en
                el teléfono</span>
            <x-ui.breadcrumbs :items="$crumbs" />
            <x-ui.breadcrumbs :items="[['label' => 'Inicio', 'href' => $here], ['label' => 'Mis reuniones']]" label="Migas de pan (corta)" />
        </div>
    </div>
</section>
