{{-- Tabs driven by URLs: status filters with counts, the pages of the profile and a long list that scrolls on the phone --}}
@php
    $link = fn(array $query): string => route('ui.index', $query) . '#ui-tabs';
    $filters = [
        ['label' => 'Todas', 'href' => $link(['estado' => 'todas']), 'count' => 24, 'current' => true],
        ['label' => 'Pendientes', 'href' => $link(['estado' => 'pendientes']), 'count' => 2],
        ['label' => 'Aceptadas', 'href' => $link(['estado' => 'aceptadas']), 'count' => 19],
        ['label' => 'Rechazadas', 'href' => $link(['estado' => 'rechazadas']), 'count' => 3],
        ['label' => 'Borradores', 'href' => $link(['estado' => 'borradores']), 'count' => 0],
    ];
    $profile = [
        ['label' => 'Personal', 'href' => $link(['perfil' => 'personal'])],
        ['label' => 'Resumen', 'href' => $link(['perfil' => 'resumen']), 'current' => true],
        ['label' => 'Contraseña', 'href' => $link(['perfil' => 'contrasena'])],
    ];
    $committees = [
        ['Presidencia', 1],
        ['Secretaría', 0],
        ['Programa', 9],
        ['Igualdad', 3],
        ['Sostenibilidad', 3],
        ['Finanzas', 4],
        ['Logística', 12],
        ['Comunicación', 7],
        ['I+D+I', 2],
    ];
    $long = [];
    foreach ($committees as $index => [$name, $count]) {
        $long[] = [
            'label' => $name,
            'href' => $link(['comite' => Str::slug($name)]),
            'count' => $count,
            'current' => $index === 2,
        ];
    }
@endphp
<section id="ui-tabs" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-tabs-title">
    <h2 id="ui-tabs-title" class="text-2xl font-bold tracking-tight">Pestañas</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Filtros con contadores</h3>
        <x-ui.tabs label="Filtrar por estado" :items="$filters" />
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Pestañas de página</h3>
        <x-ui.tabs label="Secciones del perfil" :items="$profile" size="md" />
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Lista larga deslizable</h3>
        <x-ui.tabs label="Filtrar por comité" :items="$long" />
    </div>
</section>
