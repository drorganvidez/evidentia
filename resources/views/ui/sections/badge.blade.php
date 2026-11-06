{{-- Status badges, chips, counts and the dot they share --}}
@php
    $states = [
        ['accepted', 'Aceptada', false],
        ['pending', 'Pendiente de revisión', true],
        ['rejected', 'Rechazada', false],
        ['draft', 'En borrador', false],
        ['notice', 'Convocada', false],
    ];
    $committees = [
        0 => 'Programa',
        30 => 'Logística',
        60 => 'Comunicación',
        90 => 'Patrocinio',
        120 => 'Secretaría',
        150 => 'Tesorería',
        180 => 'Diseño',
        210 => 'Web',
        240 => 'Fotografía',
        270 => 'Catering',
        300 => 'Acreditaciones',
        330 => 'Voluntariado',
    ];
    $counts = [['Pendientes', '2'], ['Mensajes', '12'], ['Archivos', '0']];
@endphp
<section id="ui-badge" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-badge-title">
    <h2 id="ui-badge-title" class="text-2xl font-bold tracking-tight">Etiquetas</h2>

    <div class="grid gap-3">
        <h3 class="text-lg font-bold tracking-tight">Estados</h3>
        <ul class="flex flex-wrap items-center gap-3">
            @foreach ($states as [$tone, $label, $pulse])
                <li><x-ui.badge :tone="$tone" :pulse="$pulse">{{ $label }}</x-ui.badge></li>
            @endforeach
            <li><x-ui.badge tone="brand">Firmada</x-ui.badge></li>
            <li><x-ui.badge tone="neutral">Sin plazo</x-ui.badge></li>
        </ul>
    </div>

    <div class="grid gap-3">
        <h3 class="text-lg font-bold tracking-tight">Tamaños</h3>
        <ul class="flex flex-wrap items-center gap-3">
            <li><x-ui.badge tone="accepted" size="sm">Aceptada</x-ui.badge></li>
            <li><x-ui.badge tone="accepted">Aceptada</x-ui.badge></li>
            <li><x-ui.badge tone="accepted" size="lg">Aceptada</x-ui.badge></li>
            <li><x-ui.badge tone="accepted" icon="shield-check" :dot="false">Íntegra</x-ui.badge></li>
            <li><x-ui.badge tone="notice" href="#ui-badge">Convocada</x-ui.badge></li>
        </ul>
    </div>

    <div class="grid gap-3">
        <h3 class="text-lg font-bold tracking-tight">Chips</h3>
        <ul class="flex flex-wrap items-center gap-3">
            <li><x-ui.badge variant="chip" mono>EV-0033</x-ui.badge></li>
            <li><x-ui.badge variant="chip" mono>AC-0012</x-ui.badge></li>
            <li><x-ui.badge variant="chip">Estudiante</x-ui.badge></li>
            <li><x-ui.badge variant="chip" icon="user-check">Coordinador</x-ui.badge></li>
            <li><x-ui.badge variant="chip" mono href="#ui-badge">EV-0034</x-ui.badge></li>
        </ul>
    </div>

    <div class="grid gap-3">
        <h3 class="text-lg font-bold tracking-tight">Comités</h3>
        <ul class="flex flex-wrap items-center gap-3">
            @foreach ($committees as $hue => $committee)
                <li><x-ui.badge variant="chip" :hue="$hue">{{ $committee }}</x-ui.badge></li>
            @endforeach
        </ul>
    </div>

    <div class="grid gap-3">
        <h3 class="text-lg font-bold tracking-tight">Contadores</h3>
        <ul class="flex flex-wrap items-center gap-6">
            @foreach ($counts as [$label, $count])
                <li class="inline-flex items-center gap-2 text-sm font-semibold">
                    {{ $label }}
                    <x-ui.badge variant="count">{{ $count }}</x-ui.badge>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="grid gap-3">
        <h3 class="text-lg font-bold tracking-tight">Puntos</h3>
        <ul class="flex flex-wrap items-center gap-x-6 gap-y-3 text-sm">
            @foreach ($states as [$tone, $label, $pulse])
                <li class="inline-flex items-center gap-2"><x-ui.dot :tone="$tone"
                        :pulse="$pulse" />{{ $label }}
                </li>
            @endforeach
            <li class="inline-flex items-center gap-2"><x-ui.dot tone="brand" />Firmada</li>
            <li class="inline-flex items-center gap-2"><x-ui.dot :hue="150" />Tesorería</li>
            <li class="inline-flex items-center gap-2"><x-ui.dot tone="accepted" size="sm" />sm</li>
            <li class="inline-flex items-center gap-2"><x-ui.dot tone="accepted" />md</li>
            <li class="inline-flex items-center gap-2"><x-ui.dot tone="accepted" size="lg" />lg</li>
        </ul>
    </div>
</section>
