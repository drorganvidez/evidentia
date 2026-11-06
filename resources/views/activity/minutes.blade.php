{{--
    Detail of a minutes, open to everybody like its PDF, and its history for its secretary, its attendees, its committee
    and the management.
--}}
@php
    $title = $meeting->title;
    $registry = 'ACTA-' . str_pad((string) $minutes->id, 4, '0', STR_PAD_LEFT);
    $type = [1 => 'Ordinaria', 2 => 'Extraordinaria'][(int) $meeting->type] ?? null;
    $modality = [1 => 'Presencial', 2 => 'Telemática', 3 => 'Híbrida'][(int) $meeting->modality] ?? null;
    $attendees = $meeting->users->sortBy(fn($user) => $user->surname . ' ' . $user->name)->values();
    $tabs = [
        [
            'label' => 'Detalle',
            'href' => route('activity.minutes', ['id' => $minutes->id]),
            'current' => $tab === 'detalle',
        ],
        [
            'label' => 'Historial',
            'href' => route('activity.minutes', ['id' => $minutes->id, 'tab' => 'historial']),
            'current' => $tab === 'historial',
        ],
    ];
@endphp
<x-layout.app :title="$title" :nav-current="$navCurrent" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => $title]]">
    <x-ui.page-header :title="$title">
        <x-slot:eyebrow>
            <x-ui.committee-chip :committee="$meeting->committee" />
            <x-ui.badge variant="chip" mono>{{ $registry }}</x-ui.badge>
        </x-slot:eyebrow>
        <x-slot:actions>
            <x-ui.button icon="file-text" :href="route('download.minutes', ['id' => $minutes->id])">Descargar PDF</x-ui.button>
            @if ($canEdit)
                <x-ui.button icon="pen-line" :href="route('secretary.meeting.manage.minutes.edit', ['id' => $minutes->id])">Editar</x-ui.button>
            @endif
            @if ($canWrite)
                <x-ui.button icon="message-square" :href="route('messages.create', ['minutes' => $minutes->id])">Escribir al secretario</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if ($canHistory)
        <x-ui.tabs class="min-w-0" label="Secciones del acta" size="md" :items="$tabs" />
    @endif

    @if ($tab === 'historial')
        <x-ui.card title="Historial" :level="2" padding="sm">
            @include('activity.partials.log', [
                'entries' => $entries,
                'viewer' => $me,
                'label' => 'Historial del acta',
            ])
        </x-ui.card>
    @else
        <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:gap-8">
            <x-ui.card title="Puntos y acuerdos" :level="2">
                @if ($minutes->points->isEmpty())
                    <x-ui.empty icon="scroll-text" title="Sin puntos" size="sm" />
                @else
                    <div class="grid gap-3">
                        @foreach ($minutes->points as $point)
                            <x-ui.disclosure :title="$point->title" :level="3">
                                <x-slot:extra>
                                    <span class="text-sm text-ink-muted">{{ (int) $point->duration }} min</span>
                                </x-slot:extra>
                                @if (filled($point->description))
                                    <p class="whitespace-pre-line">{{ $point->description }}</p>
                                @endif
                                @if ($point->agreements->isNotEmpty())
                                    <ul class="mt-3 grid gap-2">
                                        @foreach ($point->agreements as $agreement)
                                            <li class="flex flex-wrap items-start gap-2">
                                                <x-ui.badge variant="chip"
                                                    mono>{{ $agreement->identificator }}</x-ui.badge>
                                                <span class="min-w-0 flex-1">{{ $agreement->description }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </x-ui.disclosure>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
            <aside aria-label="Datos del acta" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
                <x-ui.card title="Datos" :level="2">
                    <x-ui.dl>
                        <x-ui.dl.item term="Fecha"><x-ui.time :value="$meeting->datetime" inline short /></x-ui.dl.item>
                        <x-ui.dl.item term="Duración"><x-ui.hours :value="$meeting->hours" /></x-ui.dl.item>
                        <x-ui.dl.item term="Lugar">{{ $meeting->place }}</x-ui.dl.item>
                        @if ($type)
                            <x-ui.dl.item term="Tipo">{{ $type }}</x-ui.dl.item>
                        @endif
                        @if ($modality)
                            <x-ui.dl.item term="Modalidad">{{ $modality }}</x-ui.dl.item>
                        @endif
                        <x-ui.dl.item term="Comité"><x-ui.committee-chip :committee="$meeting->committee" /></x-ui.dl.item>
                        @if ($minutes->secretary?->user)
                            <x-ui.dl.item term="Levanta acta">
                                <x-ui.person :name="$minutes->secretary->user->name" :surname="$minutes->secretary->user->surname" size="sm" />
                            </x-ui.dl.item>
                        @endif
                    </x-ui.dl>
                </x-ui.card>
                <x-ui.card title="Asistentes" :level="2">
                    <x-slot:extra>
                        <x-ui.badge variant="count">{{ $attendees->count() }}</x-ui.badge>
                    </x-slot:extra>
                    <ul class="grid gap-2">
                        @foreach ($attendees as $attendee)
                            <li><x-ui.person :name="$attendee->name" :surname="$attendee->surname" size="sm" :href="$access->profileHref($me, $attendee->id)" />
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            </aside>
        </div>
    @endif
</x-layout.app>
