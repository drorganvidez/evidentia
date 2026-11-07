{{--
    Home of the signed-in person (App\View\Dashboard): the figures of the course and what needs attention for the
    president and the lecturer, the hours and the latest evidences of whoever takes part, the review queue of a
    coordinator, and for everybody the deadlines, the next meetings, the events and the documents of each committee.
--}}
@php
    $hue = fn(int $id): int => (($id * 7) % 12) * 30;
    $stateTitles = [
        'PENDING' => ['pending', 'Enviada a revisión'],
        'ACCEPTED' => ['accepted', 'Aceptada'],
        'REJECTED' => ['rejected', 'Rechazada'],
    ];
    $hours = fn(float $value): string => number_format($value, 2, ',', '.');
    $plural = fn(int $count, string $one, string $many): string => $count . ' ' . ($count === 1 ? $one : $many);
@endphp
<x-layout.app title="Inicio">
    <x-ui.page-header :title="'Hola, ' . $name">
        @if ($upload !== null && $mine !== null)
            <x-slot:meta>
                <x-ui.deadline label="Subida de evidencias" variant="inline" verb="para subir evidencias"
                    :closes-at="$upload" />
            </x-slot:meta>
            <x-slot:actions>
                <x-ui.button variant="primary" size="lg" icon="plus" :href="route('evidence.create')" block class="md:w-auto">Nueva
                    evidencia</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if ($management !== null)
        @php
            $accepted = $management['statuses'][0];
        @endphp
        <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2 lg:grid-cols-4">
            <x-ui.stat label="Evidencias del curso" :value="$management['total']" :foot="$hours($management['hours']) . ' h aceptadas'" tone="accepted" :href="$management['routes']['evidences']"
                main class="md:col-span-2">
                <x-slot:aside>
                    <x-ui.meter shape="ring" :segments="$management['statuses']" :value="$accepted['value'] . ' %'" :label="$accepted['value'] . ' % de las evidencias enviadas están aceptadas'" />
                </x-slot:aside>
                <x-slot:legend>
                    @foreach ($management['statuses'] as $status)
                        <span class="inline-flex items-center gap-1.5"><x-ui.dot :tone="$status['tone']"
                                size="sm" />{{ $status['label'] }} {{ $status['total'] }}</span>
                    @endforeach
                </x-slot:legend>
                <x-slot:mini>
                    @foreach ($management['statuses'] as $status)
                        <span>{{ $status['label'] }} {{ $status['total'] }}</span>
                    @endforeach
                </x-slot:mini>
            </x-ui.stat>
            <x-ui.stat label="Reuniones" :value="$management['meetings']" foot="registradas este curso" tone="notice"
                :href="$management['routes']['meetings']" />
            <x-ui.stat label="Estudiantes" :value="$management['students']" foot="en el curso" :href="$management['routes']['users']" />
        </div>
    @endif

    @if ($mine !== null)
        <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2 lg:grid-cols-4">
            <x-ui.stat label="Horas computadas" :value="$hours($mine['total'])" unit="h" :foot="$plural($mine['accepted']->count(), 'evidencia aceptada', 'evidencias aceptadas')" tone="accepted"
                class="md:col-span-2">
                <x-ui.meter :segments="$mine['segments']" legend label="Horas computadas por origen" />
            </x-ui.stat>
            <x-ui.stat label="Pendientes de revisión" :value="$mine['pending']->count()" :foot="$hours($mine['pending']->sum('hours')) . ' h en revisión'" tone="pending"
                :href="route('evidence.list')" />
            <x-ui.stat label="Rechazadas" :value="$mine['rejected']->count()" :foot="$mine['drafts']->count() . ' en borrador'" tone="rejected" :href="route('evidence.list')" />
        </div>
    @endif

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            @if ($management !== null)
                <x-ui.card title="Requiere atención" :level="2" id="attention">
                    @if ($management['attention'] === [])
                        <x-ui.empty icon="circle-check" title="Todo al día" size="sm" :level="3" />
                    @else
                        <ul class="divide-y divide-rule">
                            @foreach ($management['attention'] as $item)
                                <li class="flex flex-wrap items-center gap-x-4 gap-y-1 py-3">
                                    <x-ui.dot :tone="$item['tone']" />
                                    <span class="min-w-0 flex-1">
                                        <a href="{{ $item['href'] }}"
                                            class="font-semibold text-ink hover:text-brand-ink">{{ $item['label'] }}</a>
                                        @if ($item['text'] !== null)
                                            <span class="block text-sm text-ink-muted">{{ $item['text'] }}
                                                @if ($item['closesAt'] !== null)
                                                    <x-ui.time :value="$item['closesAt']" format="relative" />
                                                @endif
                                            </span>
                                        @endif
                                    </span>
                                    <x-ui.badge variant="count">{{ $item['value'] }}</x-ui.badge>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>

                <x-ui.card title="Comités" :level="2" id="committees">
                    <x-slot:extra>
                        <x-ui.badge variant="count">{{ $management['committees']->count() }}</x-ui.badge>
                    </x-slot:extra>
                    @if ($management['committees']->isEmpty())
                        <x-ui.empty icon="network" title="No hay comités" size="sm" :level="3" />
                    @else
                        <x-ui.bar-list label="Evidencias enviadas por comité" :items="$management['committees']
                            ->sortByDesc('evidences_count')
                            ->map(
                                fn($committee) => [
                                    'label' => $committee->name,
                                    'value' => $committee->evidences_count,
                                    'hue' => $hue($committee->id),
                                    'href' => $management['routes']['committees'],
                                ],
                            )
                            ->values()" />
                    @endif
                </x-ui.card>

                <x-ui.card title="Actividad reciente" :level="2" id="activity">
                    @if ($management['activity']->isEmpty())
                        <x-ui.empty icon="history" title="Aún no hay actividad" size="sm" :level="3" />
                    @else
                        <x-ui.timeline label="Últimas evidencias enviadas o revisadas">
                            @foreach ($management['activity'] as $evidence)
                                @php
                                    [$tone, $title] = $stateTitles[$evidence->status] ?? [
                                        'draft',
                                        $evidence->status_label,
                                    ];
                                @endphp
                                <x-ui.timeline.item :tone="$tone" :title="$title"
                                    :at="$evidence->updated_at">«{{ $evidence->title }}»
                                    de {{ $evidence->user?->name }}
                                    {{ $evidence->user?->surname }}</x-ui.timeline.item>
                            @endforeach
                        </x-ui.timeline>
                    @endif
                </x-ui.card>
            @endif

            @if ($review !== null)
                <x-ui.card :title="'Por revisar en ' . $review['committee']->name" :level="2" id="review">
                    <x-slot:extra>
                        <x-ui.badge variant="count">{{ $review['count'] }}</x-ui.badge>
                    </x-slot:extra>
                    @if ($review['items']->isEmpty())
                        <x-ui.empty icon="list-checks" title="Nada por revisar" size="sm" :level="3" />
                    @else
                        <ul class="divide-y divide-rule">
                            @foreach ($review['items'] as $evidence)
                                <li class="flex flex-wrap items-center gap-x-4 gap-y-2 py-3">
                                    <a href="{{ route('coordinator.evidence.view', ['id' => $evidence->id]) }}"
                                        class="min-w-0 flex-1 font-semibold text-ink hover:text-brand-ink">{{ $evidence->title }}</a>
                                    <x-ui.person :name="$evidence->user?->name" :surname="$evidence->user?->surname" size="sm" />
                                    <x-ui.hours :value="$evidence->hours" size="sm" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <x-slot:footer>
                        <x-ui.button :href="route('coordinator.evidence.list.pending')" icon="arrow-right" size="sm" variant="quiet">Ver las
                            pendientes</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            @endif

            @if ($mine !== null)
                <x-ui.card title="Mis últimas evidencias" :level="2" id="latest">
                    <x-slot:extra>
                        <x-ui.badge variant="count">{{ $mine['count'] }}</x-ui.badge>
                    </x-slot:extra>
                    @if ($mine['latest']->isEmpty())
                        <x-ui.empty icon="file-text" title="Aún no hay evidencias" size="sm" :level="3">
                            @if ($upload !== null)
                                <x-slot:actions>
                                    <x-ui.button variant="primary" icon="plus" :href="route('evidence.create')">Nueva
                                        evidencia</x-ui.button>
                                </x-slot:actions>
                            @endif
                        </x-ui.empty>
                    @else
                        <ul class="divide-y divide-rule">
                            @foreach ($mine['latest'] as $evidence)
                                <li class="flex flex-wrap items-center gap-x-4 gap-y-2 py-3">
                                    <a href="{{ route('evidence.view', ['id' => $evidence->id]) }}"
                                        class="min-w-0 flex-1 font-semibold text-ink hover:text-brand-ink">{{ $evidence->title }}</a>
                                    <x-ui.evidence-status :status="$evidence->status" size="sm" />
                                    <x-ui.hours :value="$evidence->hours" size="sm" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <x-slot:footer>
                        <x-ui.button :href="route('evidence.list')" icon="arrow-right" size="sm" variant="quiet">Ver todas mis
                            evidencias</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            @endif

            <x-ui.card title="Convocatorias y actas" :level="2" id="documents">
                @if ($committees->isEmpty())
                    <x-ui.empty icon="scroll-text" title="No hay comités" size="sm" :level="3" />
                @else
                    <div class="grid gap-1">
                        @foreach ($committees as $committee)
                            @php
                                $requests = $committee->secretaries->flatMap->meetingRequests;
                                $minutes = $committee->secretaries->flatMap->meetingMinutes;
                            @endphp
                            <x-ui.disclosure :title="'Comité de ' . $committee->name" icon="scroll-text" name="documents" variant="plain"
                                size="sm" :level="3">
                                <x-slot:extra>
                                    <x-ui.badge
                                        variant="count">{{ $requests->count() + $minutes->count() }}</x-ui.badge>
                                </x-slot:extra>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="grid content-start gap-2">
                                        <h4 class="text-sm font-semibold text-ink-muted">Convocatorias</h4>
                                        @forelse ($requests as $request)
                                            <p class="flex flex-wrap items-baseline justify-between gap-x-3 text-md">
                                                <a href="{{ route('download.request', ['id' => $request->id]) }}"
                                                    class="font-medium text-brand-ink">{{ $request->title }}</a>
                                                <x-ui.time :value="$request->datetime" format="absolute" pattern="d/m/Y"
                                                    class="text-sm text-ink-muted" />
                                            </p>
                                        @empty
                                            <p class="text-md text-ink-muted">Sin convocatorias</p>
                                        @endforelse
                                    </div>
                                    <div class="grid content-start gap-2">
                                        <h4 class="text-sm font-semibold text-ink-muted">Actas</h4>
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
                                    </div>
                                </div>
                            </x-ui.disclosure>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>

        <aside aria-label="Plazos, reuniones y eventos" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Plazos" :level="2" id="deadlines">
                @if ($deadlines->isEmpty())
                    <x-ui.empty icon="calendar" title="Sin plazos configurados" size="sm" :level="3" />
                @else
                    <ul class="divide-y divide-rule">
                        @foreach ($deadlines as $deadline)
                            <li class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 py-2.5 text-md">
                                <span class="inline-flex items-center gap-2 font-medium">
                                    <x-ui.dot :tone="$deadline['open'] ? 'accepted' : 'draft'" />{{ $deadline['label'] }}
                                    @unless ($deadline['open'])
                                        <span class="text-sm text-ink-muted">cerrado</span>
                                    @endunless
                                </span>
                                <x-ui.time :value="$deadline['at']" format="absolute" class="text-sm" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.card title="Próximas reuniones" :level="2" id="meetings">
                @if ($meetings->isEmpty())
                    <x-ui.empty icon="handshake" title="No hay reuniones convocadas" size="sm"
                        :level="3" />
                @else
                    <ul class="divide-y divide-rule">
                        @foreach ($meetings as $meeting)
                            <li class="grid gap-1.5 py-3">
                                <span class="font-semibold">{{ $meeting->title }}</span>
                                <span class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-muted">
                                    @if ($meeting->committee)
                                        <x-ui.committee-chip :committee="$meeting->committee" />
                                    @endif
                                    <x-ui.time :value="$meeting->datetime" format="absolute" pattern="d/m H:i" />
                                    @if (filled($meeting->place))
                                        <span>{{ $meeting->place }}</span>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.card title="Eventos" :level="2" id="events">
                @if ($events->isEmpty())
                    <x-ui.empty icon="ticket" title="No hay eventos programados" size="sm"
                        :level="3" />
                @else
                    <ul class="divide-y divide-rule">
                        @foreach ($events as $event)
                            <li class="grid gap-1 py-3">
                                @if (filled($event->url))
                                    <a href="{{ $event->url }}" target="_blank" rel="noopener noreferrer"
                                        class="font-semibold text-brand-ink">{{ $event->name }}<span
                                            class="sr-only">(se abre en una pestaña nueva)</span></a>
                                @else
                                    <span class="font-semibold">{{ $event->name }}</span>
                                @endif
                                <x-ui.time :value="$event->start_datetime" format="absolute" pattern="d/m/Y H:i"
                                    class="text-sm text-ink-muted" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </aside>
    </div>
</x-layout.app>
