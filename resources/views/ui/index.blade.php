@php
    // Closed list of sections in their final order; a section renders once its file exists.
    $sections = [
        'tokens' => 'Tokens',
        'brand' => 'Marca',
        'icons' => 'Iconos',
        'accessibility' => 'Accesibilidad',
        'csp' => 'Sonda CSP',
        'button' => 'Botones',
        'badge' => 'Etiquetas',
        'avatar' => 'Personas',
        'time' => 'Fechas y horas',
        'meter' => 'Medidores',
        'card' => 'Tarjetas',
        'empty' => 'Estados vacíos',
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
        'form' => 'Formularios',
        'field' => 'Campos',
        'choice' => 'Casillas y conmutadores',
        'checkbox-group' => 'Grupos de casillas',
        'upload' => 'Subida de archivos',
        'picker' => 'Selector de personas',
        'icon-picker' => 'Selector de iconos',
        'filter-bar' => 'Filtros',
        'alert' => 'Alertas',
        'disclosure' => 'Desplegables',
        'rich-text' => 'Texto enriquecido',
        'agenda-editor' => 'Orden del día',
        'minutes-editor' => 'Editor de actas',
    ];
    $present = array_values(
        array_filter(array_keys($sections), fn(string $name): bool => view()->exists('ui.sections.' . $name)),
    );
@endphp
<x-layout.base title="Sistema de diseño">
    <x-ui.skip-link />
    <x-ui.aurora />

    <div class="mx-auto grid max-w-7xl grid-cols-[minmax(0,1fr)] gap-8 p-4 md:p-8">
        <header class="glass flex min-w-0 flex-wrap items-center gap-4 rounded-full py-2 pr-2 pl-5">
            <a href="{{ route('ui.index') }}" class="flex items-center gap-3 text-ink no-underline">
                <span aria-hidden="true"
                    class="grid size-9 place-items-center rounded-[0.8rem] bg-linear-135 from-aurora-500 to-cyan-400 text-lg font-extrabold tracking-tightest text-white shadow-glow">e</span>
                <span class="font-bold tracking-tight">Evidentia</span>
            </a>
            <h1 class="sr-only font-medium text-ink-muted sm:not-sr-only">Sistema de diseño</h1>

            <div x-data role="group" aria-label="Tema"
                class="ml-auto inline-flex gap-0.5 rounded-full border border-glass-edge bg-field p-0.75">
                @foreach (['light' => ['sun', 'Claro'], 'dark' => ['moon', 'Oscuro'], 'system' => ['monitor', 'Sistema']] as $mode => [$icon, $label])
                    <button type="button" x-on:click="$store.theme.select('{{ $mode }}')"
                        x-bind:aria-pressed="$store.theme.is('{{ $mode }}') ? 'true' : 'false'"
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-semibold text-ink-muted transition aria-pressed:bg-glass-2 aria-pressed:text-ink aria-pressed:shadow-raised">
                        <x-ui.icon :name="$icon" class="size-4" />
                        <span class="sr-only sm:not-sr-only">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
        </header>

        <nav aria-label="Contenido" class="glass rounded-xl p-3 md:p-4">
            <ul class="flex flex-wrap gap-1">
                @foreach ($present as $name)
                    <li>
                        <a href="#ui-{{ $name }}"
                            class="inline-flex rounded-full px-3 py-1.5 text-sm font-medium text-ink-muted no-underline transition hover:bg-glass-2 hover:text-ink">{{ $sections[$name] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <main id="main" tabindex="-1" class="grid grid-cols-[minmax(0,1fr)] gap-8 outline-none">
            @foreach ($present as $name)
                @include('ui.sections.' . $name)
            @endforeach
        </main>

        <footer class="glass flex flex-wrap items-center gap-x-6 gap-y-2 rounded-lg px-5 py-3 text-sm">
            <span class="inline-flex items-center gap-2 font-semibold">
                <span aria-hidden="true"
                    class="grid size-6 place-items-center rounded-lg bg-linear-135 from-aurora-500 to-cyan-400 text-sm font-extrabold text-white">e</span>
                Evidentia
            </span>
            <a href="https://github.com/drorganvidez/evidentia"
                class="rounded-full px-2.5 py-1 font-medium text-ink-muted no-underline transition hover:bg-glass-2 hover:text-ink">Repositorio</a>
            <span class="ml-auto text-ink-muted">Fase 3 · Componentes</span>
        </footer>
    </div>
</x-layout.base>
