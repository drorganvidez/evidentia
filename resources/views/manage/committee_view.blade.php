{{--
    Page of one committee for the president and the lecturers (App\View\CommitteeOverview): what is going wrong with
    the way to nudge who is in charge, the team and when each one last acted, the figures, where the hours come from,
    the members, the latest evidences and meetings, the messages to the committee and its activity.
--}}
@php
    $plural = fn(int $count, string $one, string $many): string => $count . ' ' . ($count === 1 ? $one : $many);
    $number = fn(float $value): string => number_format($value, 2, ',', '.');
    $maxWeek = max(1, max(array_column($weeks, 'value')));
    $evidenceList = fn(array $query = []): string => route(
        $prefix . '.evidence.list',
        $query + ['comite' => $committee->id],
    );
    $meetingList = route($prefix . '.meeting.list', ['comite' => $committee->id]);
    $roleNames = ['COORDINATOR' => 'Coordinación', 'SECRETARY' => 'Secretaría'];
    $toneIcons = ['rejected' => 'circle-alert', 'pending' => 'clock', 'notice' => 'info'];
    $audiences = collect($team)->unique('audience')->values();
    $evidenceColumns = [
        ['label' => 'Evidencia', 'key' => 'title'],
        ['label' => 'Autor', 'key' => 'author'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Actualizada', 'key' => 'updated_at'],
        ['label' => 'Estado', 'key' => 'status'],
    ];
@endphp
<x-layout.app :title="$committee->name" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Comités', 'href' => route($prefix . '.committee.list')],
    ['label' => $committee->name],
]">
    <x-ui.page-header :title="$committee->name">
        <x-slot:eyebrow>
            <x-ui.committee-chip :committee="$committee" size="md" />
        </x-slot:eyebrow>
        <x-slot:actions>
            <x-ui.button icon="handshake" :href="$meetingList">Reuniones</x-ui.button>
            <x-ui.button variant="primary" icon="list-checks" :href="$evidenceList()">Evidencias</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card title="Alertas" :level="2" id="signals">
        <x-slot:extra>
            <x-ui.badge :tone="$signals === [] ? 'accepted' : 'rejected'" size="sm">{{ count($signals) }}</x-ui.badge>
        </x-slot:extra>
        @if ($signals === [])
            <x-ui.empty icon="circle-check" title="Todo en orden" size="sm" :level="3" />
        @else
            <ul class="grid gap-3" aria-label="{{ 'Alertas de ' . $committee->name }}">
                @foreach ($signals as $signal)
                    <li data-signal="{{ $signal['key'] }}"
                        class="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-lg border border-rule px-4 py-3">
                        <x-ui.icon :name="$toneIcons[$signal['tone']] ?? 'info'"
                            class="{{ Arr::toCssClasses([
                                'size-5 shrink-0',
                                'text-rejected-ink' => $signal['tone'] === 'rejected',
                                'text-pending-ink' => $signal['tone'] === 'pending',
                                'text-notice-ink' => $signal['tone'] === 'notice',
                            ]) }}" />
                        <span class="grid min-w-0 flex-1 gap-0.5">
                            <span class="font-semibold">{{ $signal['title'] }}</span>
                            <span class="text-sm text-ink-muted">{{ $signal['detail'] }}</span>
                        </span>
                        @if ($signal['action'])
                            <x-ui.button size="sm" :icon="$signal['action']['icon']" :href="$signal['action']['href']">
                                {{ $signal['action']['label'] }}</x-ui.button>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    <x-ui.card title="Equipo" :level="2" id="team">
        @if ($audiences->isNotEmpty())
            <x-slot:extra>
                <span class="flex flex-wrap gap-2">
                    @foreach ($audiences as $person)
                        <x-ui.button size="sm" variant="ghost" icon="send" :href="route('messages.create', [
                            'to' => $person['audience'],
                            'subject' => $committee->name,
                        ])">
                            {{ $person['role'] === 'COORDINATOR' ? 'Escribir a la coordinación' : 'Escribir a la secretaría' }}</x-ui.button>
                    @endforeach
                </span>
            </x-slot:extra>
        @endif
        @if ($team === [])
            <x-ui.empty icon="users-round" title="Sin coordinación ni secretaría" size="sm" :level="3" />
        @else
            <ul class="grid gap-4 md:grid-cols-2" aria-label="{{ 'Equipo de ' . $committee->name }}">
                @foreach ($team as $person)
                    <li class="grid gap-2 rounded-lg border border-rule px-4 py-3">
                        <x-ui.person :name="$person['user']->name" :surname="$person['user']->surname" :role="$roleNames[$person['role']]" size="sm"
                            :href="route('profiles.view', ['id' => $person['user']->id])" />
                        <x-ui.dl dense>
                            <x-ui.dl.item term="Último acceso">
                                @if ($person['lastLogin'])
                                    <x-ui.time :value="$person['lastLogin']" format="relative" />
                                @else
                                    Nunca
                                @endif
                            </x-ui.dl.item>
                            <x-ui.dl.item term="Última acción en el comité">
                                @if ($person['lastAction'])
                                    <x-ui.time :value="$person['lastAction']" format="relative" />
                                @else
                                    Ninguna
                                @endif
                            </x-ui.dl.item>
                            @if ($person['role'] === 'COORDINATOR')
                                <x-ui.dl.item term="Última revisión">
                                    @if ($person['lastReview'])
                                        <x-ui.time :value="$person['lastReview']" format="relative" />
                                    @else
                                        Ninguna
                                    @endif
                                </x-ui.dl.item>
                            @endif
                        </x-ui.dl>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Pendientes" :value="$kpis['pending']" :foot="$kpis['pendingDays'] === null
            ? 'cola vacía'
            : ($kpis['pendingDays'] === 0
                ? 'la más antigua es de hoy'
                : 'la más antigua espera ' . $plural($kpis['pendingDays'], 'día', 'días'))" tone="pending" :href="$evidenceList(['estado' => 'pendientes'])" />
        <x-ui.stat label="Horas acreditadas" :value="$number($origins['total'])" unit="h" :foot="$plural($kpis['accepted'], 'evidencia aceptada', 'evidencias aceptadas')" tone="accepted"
            href="#origins" />
        <x-ui.stat label="Rechazadas sin corregir" :value="$kpis['rejected']" foot="a la espera de una versión nueva"
            tone="rejected" :href="$evidenceList(['estado' => 'rechazadas'])" />
        <x-ui.stat label="Miembros activos" :value="$kpis['members']" foot="con evidencias, reuniones o bonos" tone="notice"
            href="#members" />
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2 lg:gap-8">
        <x-ui.card title="Horas por origen" :level="2" id="origins">
            @if ($origins['total'] <= 0)
                <x-ui.empty icon="clock" title="Sin horas acreditadas" size="sm" :level="3" />
            @else
                <x-ui.meter :segments="$origins['segments']" legend :label="'Horas acreditadas por ' . $committee->name . ' según su origen'" />
            @endif
        </x-ui.card>

        <x-ui.card title="Envíos por semana" :level="2" id="weeks">
            <x-ui.bar-list label="Evidencias enviadas al comité en las últimas ocho semanas" :items="$weeks"
                :max="$maxWeek" />
        </x-ui.card>
    </div>

    @include('coordinator.partials.members', [
        'committee' => $committee,
        'members' => $members,
        'hours' => $kpis['hours'],
        'profiles' => true,
    ])

    <x-ui.table id="evidences" :caption="'Últimas evidencias de ' . $committee->name" show-caption :columns="$evidenceColumns">
        @if ($evidences->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="list-checks" title="Sin evidencias" size="sm" :level="3" />
            </x-slot:empty>
        @endif
        @foreach ($evidences as $evidence)
            @php
                $href = route('profiles.view.evidence', [
                    'id_user' => $evidence->user_id,
                    'id_evidence' => $evidence->id,
                ]);
            @endphp
            <x-ui.table.row :href="$href">
                <x-ui.table.cell label="Evidencia" primary>
                    <a href="{{ $href }}">{{ $evidence->title }}</a>
                </x-ui.table.cell>
                <x-ui.table.cell label="Autor">
                    @if ($evidence->user)
                        <x-ui.person :name="$evidence->user->name" :surname="$evidence->user->surname" size="sm" reverse />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Actualizada" meta hide><x-ui.time :value="$evidence->updated_at"
                        format="relative" /></x-ui.table.cell>
                <x-ui.table.cell label="Estado" end>
                    <x-ui.evidence-status :status="$evidence->status" size="sm" />
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($evidences->isNotEmpty())
            <x-slot:footer>
                <a href="{{ $evidenceList() }}" class="font-semibold text-brand-ink">Todas las evidencias de
                    {{ $committee->name }}</a>
            </x-slot:footer>
        @endif
    </x-ui.table>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2 lg:gap-8">
        <x-ui.card title="Reuniones" :level="2" id="meetings">
            <x-slot:extra>
                <a href="{{ $meetingList }}" class="text-sm font-semibold text-brand-ink">Todas</a>
            </x-slot:extra>
            <div class="grid gap-6">
                <section aria-labelledby="meetings-requests" class="grid content-start gap-2">
                    <h3 id="meetings-requests" class="text-sm font-semibold text-ink-muted">Próximas convocatorias</h3>
                    @forelse ($requests as $request)
                        <p class="flex flex-wrap items-baseline justify-between gap-x-3 text-md">
                            <a href="{{ route('activity.request', ['id' => $request->id]) }}"
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
                <section aria-labelledby="meetings-held" class="grid content-start gap-2">
                    <h3 id="meetings-held" class="text-sm font-semibold text-ink-muted">Últimas reuniones</h3>
                    @forelse ($meetings as $meeting)
                        <p class="flex flex-wrap items-baseline justify-between gap-x-3 text-md">
                            @if ($meeting->meetingMinutes)
                                <a href="{{ route('activity.minutes', ['id' => $meeting->meetingMinutes->id]) }}"
                                    class="font-medium text-brand-ink">{{ $meeting->title }}</a>
                            @else
                                <span class="font-medium">{{ $meeting->title }}</span>
                            @endif
                            <span class="text-sm text-ink-muted">
                                <x-ui.time :value="$meeting->datetime" format="absolute" pattern="d/m/Y" /> ·
                                {{ $plural($meeting->users_count, 'asistente', 'asistentes') }} ·
                                {{ $meeting->meetingMinutes ? 'con acta' : 'sin acta' }}
                            </span>
                        </p>
                    @empty
                        <p class="text-md text-ink-muted">Sin reuniones</p>
                    @endforelse
                </section>
            </div>
        </x-ui.card>

        <x-ui.card title="Mensajes al comité" :level="2" id="threads">
            @if ($threads->isEmpty())
                <x-ui.empty icon="messages-square" title="Sin mensajes" size="sm" :level="3" />
            @else
                <ul class="grid gap-3">
                    @foreach ($threads as $thread)
                        <li class="flex flex-wrap items-baseline justify-between gap-x-3 text-md">
                            @can('view', $thread)
                                <a href="{{ route('messages.show', $thread) }}"
                                    class="min-w-0 font-medium text-brand-ink">{{ $thread->subject }}</a>
                            @else
                                <span class="min-w-0 font-medium">{{ $thread->subject }}</span>
                            @endcan
                            <span class="flex items-center gap-2 text-sm text-ink-muted">
                                {{ $thread->author_name }} ·
                                <x-ui.time :value="$thread->last_message_at" format="relative" />
                                <x-ui.badge :tone="$thread->state === 'open' ? 'pending' : 'accepted'"
                                    size="sm">{{ $thread->state === 'open' ? 'Abierto' : 'Resuelto' }}</x-ui.badge>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>

    <x-ui.card title="Actividad del comité" :level="2" id="activity">
        @include('activity.partials.log', [
            'entries' => $activity,
            'viewer' => auth()->user(),
            'label' => 'Últimas acciones del comité',
        ])
    </x-ui.card>
</x-layout.app>
