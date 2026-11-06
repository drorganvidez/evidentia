{{--
    Meetings the signed-in person attended, with their hours, the way to write to the secretary of each one or of a
    meeting they are missing from, and the deadline to register meetings.
--}}
@php
    $columns = [
        ['label' => 'Reunión', 'key' => 'title'],
        ['label' => 'Comité', 'key' => 'committee', 'hide' => true],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Celebrada', 'key' => 'datetime'],
        ['label' => 'Acciones', 'srOnly' => true],
    ];
    $list = $meetings->sortByDesc('datetime');
    $deadline = \Config::meetings_timestamp();
@endphp
<x-layout.app title="Mis reuniones" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Mis reuniones']]">
    <x-ui.page-header title="Mis reuniones">
        <x-slot:actions>
            <x-ui.button icon="send" :href="\App\Services\Messaging\QuickContact::secretary(auth()->user())">Escribir a la
                secretaría</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <x-ui.table caption="Mis reuniones" :columns="$columns" :filterable="$list->isNotEmpty()" count-label="reuniones"
            filter-placeholder="Filtrar reuniones">
            @forelse ($list as $meeting)
                @php($minutesHref = $meeting->meetingMinutes ? route('activity.minutes', ['id' => $meeting->meetingMinutes->id]) : null)
                <x-ui.table.row :href="$minutesHref">
                    <x-ui.table.cell label="Reunión" primary>
                        @if ($minutesHref)
                            <a href="{{ $minutesHref }}">{{ $meeting->title }}</a>
                        @else
                            <span class="font-semibold">{{ $meeting->title }}</span>
                        @endif
                        <span class="mt-0.5 block text-sm text-ink-muted">{{ $meeting->place }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Comité" hide>
                        @if ($meeting->committee)
                            <x-ui.committee-chip :committee="$meeting->committee" />
                        @endif
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$meeting->hours" /></x-ui.table.cell>
                    <x-ui.table.cell label="Celebrada" meta>
                        <x-ui.time :value="$meeting->datetime" inline short />
                    </x-ui.table.cell>
                    <x-ui.table.cell end class="w-[1%] whitespace-nowrap">
                        <x-ui.button size="sm" variant="ghost" icon="send" icon-only :label="'Escribir a la secretaría sobre ' . $meeting->title"
                            :href="$meeting->meetingMinutes
                                ? route('messages.create', ['minutes' => $meeting->meetingMinutes->id])
                                : route(
                                    'messages.create',
                                    array_filter([
                                        'to' => $meeting->committee_id ? 'SECRETARY:' . $meeting->committee_id : null,
                                        'subject' => 'Sobre «' . $meeting->title . '»',
                                    ]),
                                )" />
                    </x-ui.table.cell>
                </x-ui.table.row>
            @empty
                <x-slot:empty>
                    <x-ui.empty icon="handshake" title="Sin reuniones" size="sm" :level="2" />
                </x-slot:empty>
            @endforelse
            @if ($list->isNotEmpty())
                <x-slot:footer>
                    <span>{{ $list->count() === 1 ? '1 reunión' : $list->count() . ' reuniones' }} ·
                        <x-ui.hours :value="$list->sum('hours')" size="sm" class="text-ink" /></span>
                </x-slot:footer>
            @endif
        </x-ui.table>
        @if ($deadline)
            <aside aria-label="Plazo de las reuniones" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
                <x-ui.deadline label="Registro de reuniones" :closes-at="\Carbon\Carbon::parse($deadline)" />
            </aside>
        @endif
    </div>
</x-layout.app>
