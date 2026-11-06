{{-- Summary of the meetings of a secretary: the figures of the three documents, the next calls and the last minutes --}}
@php
    $meetingsOpen = !\Carbon\Carbon::now()->gt(\Config::meetings_timestamp() ?? now());
    $upcoming = $meeting_requests
        ->filter(fn($request) => $request->datetime !== null && \Carbon\Carbon::parse($request->datetime)->isFuture())
        ->sortBy('datetime')
        ->take(5);
    $latest = $meeting_minutes->sortByDesc(fn($minutes) => $minutes->meeting?->datetime)->take(5);
    $signatures = $signature_sheets->sum('users_count');
    $hours = $meeting_minutes->sum(fn($minutes) => (float) ($minutes->meeting?->hours ?? 0));
@endphp
<x-layout.app title="Reuniones" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Reuniones']]">
    <x-ui.page-header title="Reuniones">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="plus" :href="route('secretary.meeting.manage.request.create')">Nueva
                convocatoria</x-ui.button>
            <x-ui.button icon="file-signature" :href="route('secretary.meeting.manage.signaturesheet.create')">Nueva hoja de
                firmas</x-ui.button>
            @if ($meetingsOpen)
                <x-ui.button icon="scroll-text" :href="route('secretary.meeting.manage.minutes.create')">Nueva
                    acta</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @include('meeting.partials.tabs')

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-3">
        <x-ui.stat label="Convocatorias" :value="$meeting_requests->count()" :foot="$upcoming->count() === 1 ? '1 por celebrar' : $upcoming->count() . ' por celebrar'" :href="route('secretary.meeting.manage.request.list')" />
        <x-ui.stat label="Hojas de firmas" :value="$signature_sheets->count()" :foot="$signatures === 1 ? '1 firma' : $signatures . ' firmas'" :href="route('secretary.meeting.manage.signaturesheet.list')" />
        <x-ui.stat label="Actas" :value="$meeting_minutes->count()" :foot="number_format($hours, 2, ',', '.') . ' h registradas'" :href="route('secretary.meeting.manage.minutes.list')" />
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2">
        <x-ui.card title="Próximas convocatorias" :level="2">
            @if ($upcoming->isEmpty())
                <x-ui.empty icon="calendar" title="Sin convocatorias por celebrar" size="sm" :level="3" />
            @else
                <ul class="divide-y divide-rule">
                    @foreach ($upcoming as $request)
                        <li class="flex flex-wrap items-center gap-x-4 gap-y-1 py-3">
                            <a href="{{ route('secretary.meeting.manage.request.edit', ['id' => $request->id]) }}"
                                class="min-w-0 flex-1 font-semibold">{{ $request->title }}</a>
                            <x-ui.time :value="$request->datetime" inline short />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card title="Últimas actas" :level="2">
            @if ($latest->isEmpty())
                <x-ui.empty icon="scroll-text" title="Sin actas" size="sm" :level="3" />
            @else
                <ul class="divide-y divide-rule">
                    @foreach ($latest as $minutes)
                        <li class="flex flex-wrap items-center gap-x-4 gap-y-1 py-3">
                            <a href="{{ route('download.minutes', ['id' => $minutes->id]) }}"
                                class="min-w-0 flex-1 font-semibold">{{ $minutes->meeting?->title }}</a>
                            <x-ui.hours :value="$minutes->meeting?->hours" size="sm" />
                            <x-ui.time :value="$minutes->meeting?->datetime" inline short />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>
</x-layout.app>
