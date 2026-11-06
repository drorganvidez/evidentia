{{-- Bars and rings of the meter component --}}
@php
    $byType = [
        ['value' => 38, 'tone' => 'accepted', 'label' => 'Evidencias', 'text' => '4,00 h'],
        ['value' => 24, 'tone' => 'notice', 'label' => 'Reuniones', 'text' => '2,50 h'],
        ['value' => 22, 'tone' => 'pending', 'label' => 'Asistencias', 'text' => '2,25 h'],
        ['value' => 16, 'tone' => 'draft', 'label' => 'Bonificaciones', 'text' => '1,75 h'],
    ];
    $byStatus = [
        ['value' => 48, 'tone' => 'accepted', 'label' => 'Aceptadas'],
        ['value' => 36, 'tone' => 'pending', 'label' => 'Pendientes'],
        ['value' => 16, 'tone' => 'rejected', 'label' => 'Rechazadas'],
    ];
    $deadline = [['value' => 68, 'tone' => 'brand']];
@endphp
<section id="ui-meter" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-meter-title">
    <h2 id="ui-meter-title" class="text-2xl font-bold tracking-tight">Medidores</h2>

    <h3 class="text-lg font-bold tracking-tight">Barras</h3>
    <div class="grid grid-cols-[minmax(0,1fr)] gap-3 lg:grid-cols-2">
        <div class="glass-2 grid gap-3 rounded-md p-4">
            <span class="font-mono text-2xs text-ink-muted">bar · gradient · label</span>
            <p class="flex flex-wrap items-baseline justify-between gap-x-4 text-sm">
                <span class="font-semibold">Plazo de subida de evidencias</span>
                <span class="text-ink-muted">Quedan 9 días</span>
            </p>
            <x-ui.meter :segments="$deadline" gradient label="68 % del plazo consumido" />
        </div>
        <div class="glass-2 grid gap-3 rounded-md p-4">
            <span class="font-mono text-2xs text-ink-muted">bar · size="sm" · track=false</span>
            <p class="flex flex-wrap items-baseline justify-between gap-x-4 text-sm">
                <span class="font-semibold">Plazo de reuniones</span>
                <span class="text-ink-muted">Quedan 23 días</span>
            </p>
            <x-ui.meter :segments="[['value' => 25, 'tone' => 'notice']]" size="sm" :track="false" label="25 % del plazo consumido" />
        </div>
        <div class="glass-2 grid gap-3 rounded-md p-4">
            <span class="font-mono text-2xs text-ink-muted">bar · cuatro tonos · legend</span>
            <p class="flex flex-wrap items-baseline justify-between gap-x-4 text-sm">
                <span class="font-semibold">Horas computadas</span>
                <x-ui.hours :value="10.5" size="sm" />
            </p>
            <x-ui.meter :segments="$byType" legend />
        </div>
        <div class="glass-2 grid gap-3 rounded-md p-4">
            <span class="font-mono text-2xs text-ink-muted">bar · tres tonos · label · size="sm"</span>
            <p class="flex flex-wrap items-baseline justify-between gap-x-4 text-sm">
                <span class="font-semibold">Evidencias por estado</span>
                <span class="text-ink-muted">25 evidencias</span>
            </p>
            <x-ui.meter :segments="$byStatus" size="sm"
                label="Evidencias por estado: 48 % aceptadas, 36 % pendientes, 16 % rechazadas" />
        </div>
    </div>

    <h3 class="text-lg font-bold tracking-tight">Anillos</h3>
    <div class="grid grid-cols-[minmax(0,1fr)] gap-3 md:grid-cols-2 lg:grid-cols-4">
        <div class="glass-2 grid justify-items-center gap-3 rounded-md p-4">
            <span class="justify-self-start font-mono text-2xs text-ink-muted">ring · value · label</span>
            <x-ui.meter shape="ring" :segments="$byStatus" value="48 %"
                label="48 % de las horas declaradas están aceptadas" />
            <span class="text-sm text-ink-muted">Horas aceptadas</span>
        </div>
        <div class="glass-2 grid justify-items-center gap-3 rounded-md p-4">
            <span class="justify-self-start font-mono text-2xs text-ink-muted">ring · size="sm" · sin label</span>
            <x-ui.meter shape="ring" :segments="$byStatus" value="48 %" size="sm" />
            <span class="text-sm text-ink-muted">48 % de las horas aceptadas</span>
        </div>
        <div class="glass-2 grid justify-items-center gap-3 rounded-md p-4">
            <span class="justify-self-start font-mono text-2xs text-ink-muted">ring · un segmento</span>
            <x-ui.meter shape="ring" :segments="$deadline" value="68 %" label="68 % del plazo consumido" />
            <span class="text-sm text-ink-muted">Plazo de subida</span>
        </div>
        <div class="glass-2 grid justify-items-center gap-3 rounded-md p-4">
            <span class="justify-self-start font-mono text-2xs text-ink-muted">ring · track=false</span>
            <x-ui.meter shape="ring" :segments="$byType" :track="false" value="10,5 h"
                label="10,5 horas computadas: 38 % evidencias, 24 % reuniones, 22 % asistencias, 16 % bonificaciones" />
            <span class="text-sm text-ink-muted">Horas computadas</span>
        </div>
    </div>
</section>
