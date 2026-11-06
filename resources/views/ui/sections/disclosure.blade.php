{{-- Disclosures: an exclusive accordion of committees, an open one with extra content, the plain variant and the small size --}}
@php
    $committees = [
        ['name' => 'Comité de Programa', 'icon' => 'scroll-text', 'count' => 4, 'open' => true],
        ['name' => 'Comité de Logística', 'icon' => 'map-pin', 'count' => 2, 'open' => false],
        ['name' => 'Comité de Comunicación', 'icon' => 'message-square', 'count' => 0, 'open' => false],
    ];
    $documents = [
        ['title' => 'Convocatoria de la reunión 3', 'date' => now()->subDays(12)],
        ['title' => 'Acta de la reunión 2', 'date' => now()->subDays(26)],
    ];
    $editions = ['2025', '2024', '2023'];
@endphp
<section id="ui-disclosure" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-disclosure-title">
    <h2 id="ui-disclosure-title" class="text-2xl font-bold tracking-tight">Desplegables</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2">
        <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
            <h3 class="text-lg font-bold tracking-tight">Acordeón exclusivo</h3>
            @foreach ($committees as $committee)
                <x-ui.disclosure :title="$committee['name']" :icon="$committee['icon']" name="ui-committees" :open="$committee['open']"
                    :level="4">
                    <x-slot:extra>
                        <x-ui.badge variant="count">{{ $committee['count'] }}</x-ui.badge>
                    </x-slot:extra>
                    @if ($committee['count'] > 0)
                        <ul class="grid gap-2 text-sm">
                            @foreach ($documents as $document)
                                <li class="flex min-w-0 flex-wrap items-center justify-between gap-x-3 gap-y-1">
                                    <span class="font-medium">{{ $document['title'] }}</span>
                                    <x-ui.time :value="$document['date']" format="absolute" pattern="d/m/Y"
                                        class="text-xs text-ink-muted" />
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-ink-muted">Este comité todavía no tiene documentos.</p>
                    @endif
                </x-ui.disclosure>
            @endforeach
        </div>

        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-3">
            <h3 class="text-lg font-bold tracking-tight">Abierto, liso y pequeño</h3>
            <x-ui.disclosure title="Ver ediciones anteriores" icon="history" open :level="4">
                <x-slot:extra>
                    <span class="text-sm text-ink-muted">{{ count($editions) }} ediciones</span>
                </x-slot:extra>
                <ul class="flex flex-wrap gap-2">
                    @foreach ($editions as $edition)
                        <li><x-ui.badge variant="chip" mono>{{ $edition }}</x-ui.badge></li>
                    @endforeach
                </ul>
            </x-ui.disclosure>
            <x-ui.disclosure title="Cómo se calculan las horas" variant="plain" :level="4">
                <p class="text-sm text-ink-muted">Cada evidencia aceptada suma sus horas al total de la edición.</p>
            </x-ui.disclosure>
            <x-ui.disclosure title="Detalles técnicos" size="sm" icon="file-text">
                <p class="text-sm text-ink-muted">Tamaño pequeño con el título como texto, sin nivel de encabezado.</p>
            </x-ui.disclosure>
        </div>
    </div>
</section>
