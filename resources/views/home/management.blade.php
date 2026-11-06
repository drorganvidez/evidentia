{{--
    Control panel of the course for the president and the lecturer (App\View\ManagementDashboard): the figures, how the
    hours spread, each committee, the submissions by week, the review queue and the traceability of the activity log.
--}}
@php
    $hue = fn(int $id): int => (($id * 7) % 12) * 30;
    $hours = fn(float $value): string => number_format($value, 2, ',', '.');
    $plural = fn(int $count, string $one, string $many): string => $count . ' ' . ($count === 1 ? $one : $many);
    $maxWeek = max(1, max(array_column($weeks, 'value')));
@endphp
<x-layout.app title="Panel del curso" :breadcrumbs="[['label' => 'Panel del curso']]">
    <x-ui.page-header title="Panel del curso" :lede="'Hola, ' . $name">
        <x-slot:actions>
            <x-ui.button icon="download" :href="$routes['export']">Exportar</x-ui.button>
            <x-ui.button variant="primary" icon="list-checks" :href="$routes['review']">Revisar evidencias</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- The four figures of the course --}}
    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Horas acreditadas" :value="$hours($kpis['hours'])" unit="h" :foot="'media de ' . $hours($kpis['average']) . ' h por estudiante'" tone="accepted"
            :href="$routes['users']" />
        <x-ui.stat label="Participación" :value="$kpis['participation'] . ' %'" :foot="$kpis['participating'] . ' de ' . $kpis['students'] . ' estudiantes con evidencias'" tone="notice" :href="$routes['users']" />
        <x-ui.stat label="Por revisar" :value="$kpis['pending']" :foot="$kpis['pendingDays'] === null
            ? 'cola vacía'
            : ($kpis['pendingDays'] === 0
                ? 'la más antigua es de hoy'
                : 'la más antigua espera ' . $plural($kpis['pendingDays'], 'día', 'días'))" tone="pending" :href="$routes['evidences']" />
        <x-ui.stat label="Aceptación" :value="$kpis['acceptance'] === null ? '—' : $kpis['acceptance'] . ' %'" :foot="$plural($kpis['rejected'], 'evidencia rechazada', 'evidencias rechazadas')" tone="rejected" :href="$routes['evidences']" />
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:gap-8">
        <x-ui.card title="Horas por estudiante" :level="2" id="distribution">
            <x-slot:extra>
                <span
                    class="text-sm text-ink-muted">{{ $plural($kpis['students'], 'estudiante', 'estudiantes') }}</span>
            </x-slot:extra>
            <x-ui.bar-list label="Estudiantes por tramo de horas acreditadas" :items="$distribution" />
        </x-ui.card>

        <x-ui.card title="Origen de las horas" :level="2" id="origins">
            <div class="grid gap-5">
                <p class="flex items-baseline gap-2">
                    <x-ui.hours :value="$kpis['hours']" size="xl" class="font-bold tracking-tighter" />
                    <span class="text-sm text-ink-muted">en total</span>
                </p>
                <x-ui.meter :segments="$origins" legend label="Horas acreditadas por origen" />
            </div>
        </x-ui.card>
    </div>

    <x-ui.table caption="Comités" show-caption :columns="[
        ['label' => 'Comité', 'key' => 'name'],
        ['label' => 'Coordinación', 'key' => 'coordinators'],
        ['label' => 'Evidencias', 'key' => 'state'],
        ['label' => 'Pendientes', 'key' => 'pending', 'align' => 'end'],
        ['label' => 'Más antigua', 'key' => 'oldest'],
    ]">
        @foreach ($committees as $committee)
            @php($committeeHref = str_replace('__id__', (string) $committee['id'], $routes['committee']))
            <x-ui.table.row :href="$committeeHref">
                <x-ui.table.cell label="Comité" primary>
                    <span class="inline-flex items-center gap-2">
                        <span class="hue-{{ $hue($committee['id']) }} committee-dot" aria-hidden="true"></span>
                        <a href="{{ $committeeHref }}">{{ $committee['name'] }}</a>
                    </span>
                </x-ui.table.cell>
                <x-ui.table.cell label="Coordinación" meta>
                    @if ($committee['coordinators'] !== '')
                        {{ $committee['coordinators'] }}
                    @else
                        <x-ui.badge tone="pending" size="sm">Sin coordinación</x-ui.badge>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Evidencias">
                    @if ($committee['total'] > 0)
                        <div class="grid min-w-40 gap-1">
                            <x-ui.meter :segments="$committee['segments']" size="sm" :label="'Evidencias de ' .
                                $committee['name'] .
                                ': ' .
                                $committee['segments'][0]['text'] .
                                ' aceptadas, ' .
                                $committee['segments'][1]['text'] .
                                ' pendientes y ' .
                                $committee['segments'][2]['text'] .
                                ' rechazadas'" />
                            <span class="text-xs text-ink-muted">{{ $committee['segments'][0]['text'] }} aceptadas ·
                                {{ $committee['segments'][2]['text'] }} rechazadas</span>
                        </div>
                    @else
                        <span class="text-sm text-ink-muted">Sin evidencias</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Pendientes" numeric>{{ $committee['pending'] }}</x-ui.table.cell>
                <x-ui.table.cell label="Más antigua" meta>
                    @if ($committee['oldest'])
                        <x-ui.time :value="$committee['oldest']" format="relative" />
                    @else
                        <span class="text-ink-muted">—</span>
                    @endif
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($committees->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="network" title="No hay comités" size="sm" />
            </x-slot:empty>
        @endif
    </x-ui.table>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:gap-8">
        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Actividad reciente" :level="2" id="activity">
                @include('activity.partials.log', [
                    'entries' => $activity,
                    'viewer' => auth()->user(),
                    'label' => 'Últimas acciones del registro de actividad',
                ])
            </x-ui.card>

            <x-ui.card title="Envíos por semana" :level="2" id="weeks">
                <x-ui.bar-list label="Evidencias enviadas en las últimas ocho semanas" :items="$weeks"
                    :max="$maxWeek" />
            </x-ui.card>
        </div>

        <aside aria-label="Trazabilidad y plazos" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Señales del registro" :level="2" id="signals">
                <x-slot:extra>
                    <span class="text-sm text-ink-muted">30 días</span>
                </x-slot:extra>
                <ul class="grid gap-3">
                    @foreach ($signals as $signal)
                        <li class="flex items-center gap-3">
                            <x-ui.dot :tone="$signal['value'] > 0 ? $signal['tone'] : 'draft'" />
                            <span class="min-w-0 flex-1">{{ $signal['label'] }}</span>
                            <x-ui.badge variant="count">{{ $signal['value'] }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>

            <x-ui.card title="Accesos" :level="2" id="access">
                <x-slot:extra>
                    <span class="text-sm text-ink-muted">7 días</span>
                </x-slot:extra>
                <x-ui.dl dense>
                    <x-ui.dl.item term="Inicios">{{ $access['logins'] }}</x-ui.dl.item>
                    <x-ui.dl.item term="Personas">{{ $access['people'] }}</x-ui.dl.item>
                    <x-ui.dl.item term="Fallidos">
                        @if ($access['failed'] > 0)
                            <x-ui.badge tone="rejected" size="sm">{{ $access['failed'] }}</x-ui.badge>
                        @else
                            0
                        @endif
                    </x-ui.dl.item>
                </x-ui.dl>
            </x-ui.card>

            @if ($serverErrors)
                <x-ui.card title="Errores del servidor" :level="2" id="errors">
                    <x-slot:extra>
                        <x-ui.badge :tone="$serverErrors['open'] > 0 ? 'rejected' : 'accepted'" size="sm">{{ $serverErrors['open'] }}
                            abiertos</x-ui.badge>
                    </x-slot:extra>
                    @if ($serverErrors['latest']->isEmpty())
                        <x-ui.empty icon="circle-check" title="Sin errores abiertos" size="sm"
                            :level="3" />
                    @else
                        <ul class="grid gap-3">
                            @foreach ($serverErrors['latest'] as $report)
                                <li class="grid gap-0.5">
                                    <a href="{{ route($serverErrors['show'], $report->id) }}"
                                        class="font-semibold text-ink hover:text-brand-ink">{{ $report->shortException() }}</a>
                                    <span
                                        class="text-sm text-ink-muted">{{ $plural($report->occurrences, 'vez', 'veces') }}
                                        ·
                                        <x-ui.time :value="$report->last_seen_at" format="relative" /></span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>
            @endif

            <x-ui.card title="Cola de presidencia" :level="2" id="presidency">
                <x-slot:extra>
                    <x-ui.badge variant="count">{{ $presidency['count'] }}</x-ui.badge>
                </x-slot:extra>
                @if ($presidency['items']->isEmpty())
                    <x-ui.empty icon="circle-check" title="Nada por revisar" size="sm" :level="3" />
                @else
                    <ul class="grid gap-3" aria-label="Evidencias que esperan a la presidencia">
                        @foreach ($presidency['items'] as $evidence)
                            <li class="grid gap-0.5">
                                <a href="{{ route('president.review.view', ['id' => $evidence->id]) }}"
                                    class="font-semibold text-ink hover:text-brand-ink pointer-coarse:inline-flex pointer-coarse:min-h-11 pointer-coarse:items-center">{{ $evidence->title }}</a>
                                <span class="text-sm text-ink-muted">{{ $evidence->user?->name }}
                                    {{ $evidence->user?->surname }} · {{ $evidence->committee?->name }} ·
                                    <x-ui.time :value="$evidence->updated_at" format="relative" /></span>
                            </li>
                        @endforeach
                    </ul>
                    <x-slot:footer>
                        <x-ui.button size="sm" variant="quiet" icon="stamp" :href="$presidency['href']">Abrir la
                            cola</x-ui.button>
                    </x-slot:footer>
                @endif
            </x-ui.card>

            <x-ui.card title="Plazos" :level="2" id="deadlines">
                @if ($deadlines->isEmpty())
                    <x-ui.empty icon="calendar" title="Sin plazos fijados" size="sm" :level="3" />
                @else
                    <x-ui.timeline label="Plazos del curso">
                        @foreach ($deadlines as $deadline)
                            <x-ui.timeline.item :tone="$deadline['open'] ? 'accepted' : 'draft'" :title="$deadline['label']" :at="$deadline['at']" />
                        @endforeach
                    </x-ui.timeline>
                @endif
            </x-ui.card>
        </aside>
    </div>
</x-layout.app>
