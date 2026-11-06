{{--
    «Mi comité», the workspace of a coordinator (App\View\CommitteePanel): the team, the figures of the committee, its
    members with the hours it credits them, how those hours spread, the submissions by week, its meetings and the
    latest annotations of the activity log about it.
--}}
@php
    $plural = fn(int $count, string $one, string $many): string => $count . ' ' . ($count === 1 ? $one : $many);
    $hours = fn(float $value): string => number_format($value, 2, ',', '.');
    $maxWeek = max(1, max(array_column($weeks, 'value')));
    $columns = [
        ['label' => 'Miembro', 'key' => 'member'],
        ['label' => 'Evidencias', 'key' => 'evidences'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Reuniones', 'key' => 'meetings', 'align' => 'end'],
        ['label' => 'Última actividad', 'key' => 'last'],
    ];
@endphp
<x-layout.app title="Mi comité" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Mi comité']]">
    <x-ui.page-header title="Mi comité">
        <x-slot:eyebrow>
            <x-ui.committee-chip :committee="$committee" size="md" />
        </x-slot:eyebrow>
        <x-slot:actions>
            <x-ui.button icon="download" :href="route('coordinator.committee.export')">Exportar</x-ui.button>
            <x-ui.button variant="primary" icon="list-checks" :href="route('coordinator.evidence.list.pending')">Revisar
                pendientes</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <ul aria-label="{{ 'Equipo de ' . $committee->name }}"
        class="glass flex flex-wrap items-center gap-x-8 gap-y-3 rounded-xl px-5 py-4">
        @foreach ($coordinators as $person)
            <li><x-ui.person :name="$person->name" :surname="$person->surname" role="Coordinación" size="sm" /></li>
        @endforeach
        @forelse ($secretaries as $person)
            <li><x-ui.person :name="$person->name" :surname="$person->surname" role="Secretaría" size="sm" /></li>
        @empty
            <li><x-ui.badge tone="pending" size="sm">Sin secretaría</x-ui.badge></li>
        @endforelse
    </ul>

    {{-- The four figures of the committee --}}
    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Pendientes" :value="$kpis['pending']" :foot="$kpis['pendingDays'] === null
            ? 'cola vacía'
            : ($kpis['pendingDays'] === 0
                ? 'la más antigua es de hoy'
                : 'la más antigua espera ' . $plural($kpis['pendingDays'], 'día', 'días'))" tone="pending" :href="route('coordinator.evidence.list.pending')" />
        <x-ui.stat label="Horas aceptadas" :value="$hours($kpis['acceptedHours'])" unit="h" :foot="$plural($kpis['accepted'], 'evidencia aceptada', 'evidencias aceptadas') .
            ' · ' .
            $hours($kpis['hours']) .
            ' h acreditadas'" tone="accepted"
            :href="route('coordinator.evidence.list.accepted')" />
        <x-ui.stat label="Rechazadas sin corregir" :value="$kpis['rejected']" foot="a la espera de una versión nueva"
            tone="rejected" :href="route('coordinator.evidence.list.rejected')" />
        <x-ui.stat label="Miembros activos" :value="$kpis['members']" foot="con evidencias, reuniones o bonos" tone="notice"
            href="#members" />
    </div>

    <x-ui.table id="members" :caption="'Miembros de ' . $committee->name" show-caption :columns="$columns" :filterable="$members->isNotEmpty()"
        count-label="miembros" filter-placeholder="Filtrar miembros">
        @if ($members->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="users" title="Sin miembros todavía" size="sm" :level="3" />
            </x-slot:empty>
        @endif
        @foreach ($members as $member)
            <x-ui.table.row :search="implode(
                ' ',
                array_filter([
                    $member['surname'],
                    $member['name'],
                    $member['username'],
                    $member['email'],
                    $member['pending'] > 0 ? 'pendiente' : null,
                    $member['rejected'] > 0 ? 'rechazada' : null,
                ]),
            )">
                <x-ui.table.cell label="Miembro" primary header>
                    <x-ui.person :name="$member['name']" :surname="$member['surname']" :role="$member['username']" size="sm" reverse />
                </x-ui.table.cell>
                <x-ui.table.cell label="Evidencias" full>
                    @if ($member['accepted'] + $member['pending'] + $member['rejected'] === 0)
                        <span class="text-sm text-ink-muted">Sin evidencias</span>
                    @else
                        <span class="flex flex-wrap items-center gap-1.5">
                            @if ($member['accepted'] > 0)
                                <x-ui.badge tone="accepted"
                                    size="sm">{{ $plural($member['accepted'], 'aceptada', 'aceptadas') }}</x-ui.badge>
                            @endif
                            @if ($member['pending'] > 0)
                                <x-ui.badge tone="pending"
                                    size="sm">{{ $plural($member['pending'], 'pendiente', 'pendientes') }}</x-ui.badge>
                            @endif
                            @if ($member['rejected'] > 0)
                                <x-ui.badge tone="rejected"
                                    size="sm">{{ $plural($member['rejected'], 'rechazada', 'rechazadas') }}</x-ui.badge>
                            @endif
                        </span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric><x-ui.hours :value="$member['hours']" /></x-ui.table.cell>
                <x-ui.table.cell label="Reuniones" numeric meta end>{{ $member['meetings'] }}<span class="md:hidden">
                        {{ $member['meetings'] === 1 ? 'reunión' : 'reuniones' }}</span></x-ui.table.cell>
                <x-ui.table.cell label="Última actividad" meta>
                    @if ($member['last'])
                        <x-ui.time :value="$member['last']" format="relative" />
                    @else
                        —
                    @endif
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($members->isNotEmpty())
            <x-slot:footer>
                <span>{{ $plural($members->count(), 'miembro', 'miembros') }} ·
                    <x-ui.hours :value="$kpis['hours']" size="sm" class="text-ink" /></span>
            </x-slot:footer>
        @endif
    </x-ui.table>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2 lg:gap-8">
        <x-ui.card title="Horas por miembro" :level="2" id="distribution">
            @if ($distribution === [])
                <x-ui.empty icon="clock" title="Sin horas acreditadas" size="sm" :level="3" />
            @else
                <x-ui.bar-list label="Horas acreditadas por el comité a sus miembros" :items="$distribution"
                    value-format="hours" />
            @endif
        </x-ui.card>

        <x-ui.card title="Envíos por semana" :level="2" id="weeks">
            <x-ui.bar-list label="Evidencias enviadas al comité en las últimas ocho semanas" :items="$weeks"
                :max="$maxWeek" />
        </x-ui.card>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)] lg:gap-8">
        <x-ui.card title="Reuniones del comité" :level="2" id="meetings">
            <div class="grid gap-6">
                <section aria-labelledby="meetings-requests" class="grid content-start gap-2">
                    <h3 id="meetings-requests" class="text-sm font-semibold text-ink-muted">Próximas convocatorias</h3>
                    @forelse ($requests as $request)
                        <p class="flex flex-wrap items-baseline justify-between gap-x-3 text-md">
                            <a href="{{ route('download.request', ['id' => $request->id]) }}"
                                class="font-medium text-brand-ink">{{ $request->title }}</a>
                            <span class="text-sm text-ink-muted">
                                <x-ui.time :value="$request->datetime" format="absolute" pattern="d/m H:i" />
                                @if (filled($request->place))
                                    · {{ $request->place }}
                                @endif
                            </span>
                        </p>
                    @empty
                        <p class="text-md text-ink-muted">Sin convocatorias próximas</p>
                    @endforelse
                </section>
                <section aria-labelledby="meetings-minutes" class="grid content-start gap-2">
                    <h3 id="meetings-minutes" class="text-sm font-semibold text-ink-muted">Últimas actas</h3>
                    @forelse ($minutes as $minute)
                        <p class="flex flex-wrap items-baseline justify-between gap-x-3 text-md">
                            <a href="{{ route('download.minutes', ['id' => $minute->id]) }}"
                                class="font-medium text-brand-ink">{{ $minute->meeting?->title }}</a>
                            <x-ui.time :value="$minute->meeting?->datetime" format="absolute" pattern="d/m/Y"
                                class="text-sm text-ink-muted" />
                        </p>
                    @empty
                        <p class="text-md text-ink-muted">Sin actas</p>
                    @endforelse
                </section>
            </div>
        </x-ui.card>

        <x-ui.card title="Actividad del comité" :level="2" id="activity">
            @include('activity.partials.log', [
                'entries' => $activity,
                'viewer' => auth()->user(),
                'label' => 'Últimas acciones del comité',
            ])
        </x-ui.card>
    </div>
</x-layout.app>
