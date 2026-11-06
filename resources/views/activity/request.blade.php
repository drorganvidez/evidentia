{{--
    Detail of a meeting request, open to everybody like its PDF, and its history (with its signature sheet) for its
    secretary, its committee and the management. Who signed is only named to the secretary and the management.
--}}
@php
    $sheet = $meetingRequest->signatureSheet;
    $held =
        $meetingRequest->datetime !== null && \Illuminate\Support\Carbon::parse($meetingRequest->datetime)->isPast();
    $type = [1 => 'Ordinaria', 2 => 'Extraordinaria'][(int) $meetingRequest->type] ?? null;
    $modality = [1 => 'Presencial', 2 => 'Telemática', 3 => 'Híbrida'][(int) $meetingRequest->modality] ?? null;
    $points = $meetingRequest->diary?->diaryPoints ?? collect();
    $tabs = [
        [
            'label' => 'Detalle',
            'href' => route('activity.request', ['id' => $meetingRequest->id]),
            'current' => $tab === 'detalle',
        ],
        [
            'label' => 'Historial',
            'href' => route('activity.request', ['id' => $meetingRequest->id, 'tab' => 'historial']),
            'current' => $tab === 'historial',
        ],
    ];
@endphp
<x-layout.app :title="$meetingRequest->title" :nav-current="$navCurrent" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => $meetingRequest->title]]">
    <x-ui.page-header :title="$meetingRequest->title">
        <x-slot:eyebrow>
            <x-ui.committee-chip :committee="$meetingRequest->committee" />
            <x-ui.badge :tone="$held ? 'draft' : 'pending'" size="sm">{{ $held ? 'Celebrada' : 'Programada' }}</x-ui.badge>
        </x-slot:eyebrow>
        <x-slot:actions>
            <x-ui.button icon="file-text" :href="route('download.request', ['id' => $meetingRequest->id])">Descargar PDF</x-ui.button>
            @if ($isSecretary)
                <x-ui.button icon="pen-line" :href="route('secretary.meeting.manage.request.edit', ['id' => $meetingRequest->id])">Editar</x-ui.button>
                @if ($sheet)
                    <x-ui.button icon="file-signature" :href="route('secretary.meeting.manage.signaturesheet.view', ['id' => $sheet->id])">Hoja de firmas</x-ui.button>
                @endif
            @else
                <x-ui.button icon="message-square" :href="route('messages.create', ['request' => $meetingRequest->id])">Escribir al secretario</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if ($canHistory)
        <x-ui.tabs class="min-w-0" label="Secciones de la convocatoria" size="md" :items="$tabs" />
    @endif

    @if ($tab === 'historial')
        <x-ui.card title="Historial" :level="2" padding="sm">
            @include('activity.partials.log', [
                'entries' => $entries,
                'viewer' => $me,
                'label' => 'Historial de la convocatoria',
            ])
        </x-ui.card>
    @else
        <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:gap-8">
            <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
                <x-ui.card title="Orden del día" :level="2">
                    @if ($points->isEmpty())
                        <x-ui.empty icon="list-checks" title="Sin puntos" size="sm" />
                    @else
                        <ol class="grid list-decimal gap-1 pl-5">
                            @foreach ($points as $point)
                                <li>{{ $point->point }}</li>
                            @endforeach
                        </ol>
                    @endif
                </x-ui.card>
                @if ($sheet && $seesSignatures)
                    <x-ui.card title="Firmas" :level="2">
                        <x-slot:extra>
                            <x-ui.badge variant="count">{{ $sheet->users->count() }}</x-ui.badge>
                        </x-slot:extra>
                        @if ($sheet->users->isEmpty())
                            <x-ui.empty icon="file-signature" title="Sin firmas" size="sm" />
                        @else
                            <ul class="grid gap-2">
                                @foreach ($sheet->users as $signer)
                                    <li class="flex flex-wrap items-center justify-between gap-2">
                                        <x-ui.person :name="$signer->name" :surname="$signer->surname" size="sm"
                                            :href="$access->profileHref($me, $signer->id)" />
                                        <x-ui.time :value="$signer->pivot->created_at" inline short />
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </x-ui.card>
                @endif
            </div>
            <aside aria-label="Datos de la convocatoria" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
                <x-ui.card title="Datos" :level="2">
                    <x-ui.dl>
                        <x-ui.dl.item term="Fecha y hora"><x-ui.time :value="$meetingRequest->datetime" inline short /></x-ui.dl.item>
                        <x-ui.dl.item term="Lugar">{{ $meetingRequest->place }}</x-ui.dl.item>
                        @if ($type)
                            <x-ui.dl.item term="Tipo">{{ $type }}</x-ui.dl.item>
                        @endif
                        @if ($modality)
                            <x-ui.dl.item term="Modalidad">{{ $modality }}</x-ui.dl.item>
                        @endif
                        <x-ui.dl.item term="Comité"><x-ui.committee-chip :committee="$meetingRequest->committee" /></x-ui.dl.item>
                        @if ($meetingRequest->secretary?->user)
                            <x-ui.dl.item term="Convoca">
                                <x-ui.person :name="$meetingRequest->secretary->user->name" :surname="$meetingRequest->secretary->user->surname" size="sm" />
                            </x-ui.dl.item>
                        @endif
                        @if ($sheet && !$seesSignatures)
                            <x-ui.dl.item term="Firmas">{{ $sheet->users->count() }}</x-ui.dl.item>
                        @endif
                    </x-ui.dl>
                </x-ui.card>
            </aside>
        </div>
    @endif
</x-layout.app>
