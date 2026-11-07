{{--
    The events the student signed up for, with the hours each one counts, whether they attended and the way to write
    to the register coordination about any of them.
--}}
@php
    $columns = $sort->columns([
        ['label' => 'Evento', 'key' => 'event'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Empieza', 'key' => 'start'],
        ['label' => 'Termina', 'key' => 'end', 'hide' => true],
        ['label' => 'Asistencia', 'key' => 'status'],
        ['label' => 'Acciones', 'srOnly' => true],
    ]);
    $attended = $attendees->where('status', 'Checked In');
@endphp
<x-layout.app title="Mis asistencias" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Mis asistencias']]">
    <x-ui.page-header title="Mis asistencias">
        <x-slot:actions>
            <x-ui.button icon="send" :href="\App\Services\Messaging\QuickContact::register()">Escribir a la coordinación de
                registro</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Mis asistencias" :columns="$columns">
        @foreach ($attendees as $attendee)
            <x-ui.table.row>
                <x-ui.table.cell label="Evento" primary>
                    @if (filled($attendee->event->url))
                        <a href="{{ $attendee->event->url }}" target="_blank"
                            rel="noopener noreferrer">{{ $attendee->event->name }}<span class="sr-only">(se abre en una
                                pestaña nueva)</span></a>
                    @else
                        {{ $attendee->event->name }}
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$attendee->event->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Empieza" meta><x-ui.time :value="$attendee->event->start_datetime"
                        format="absolute" /></x-ui.table.cell>
                <x-ui.table.cell label="Termina" meta hide><x-ui.time :value="$attendee->event->end_datetime"
                        format="absolute" /></x-ui.table.cell>
                <x-ui.table.cell label="Asistencia" end><x-ui.attendee-status :status="$attendee->status"
                        size="sm" /></x-ui.table.cell>
                <x-ui.table.cell tools>
                    <x-ui.button size="sm" variant="ghost" icon="send" icon-only :label="'Escribir a la coordinación de registro sobre ' . $attendee->event->name"
                        :href="route('messages.create', [
                            'to' => 'REGISTER_COORDINATOR',
                            'subject' => 'Sobre mi asistencia a «' . $attendee->event->name . '»',
                        ])" />
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($attendees->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="ticket" title="Aún no hay asistencias" size="sm" :level="2" />
            </x-slot:empty>
        @else
            <x-slot:footer>
                <span>{{ $attended->count() }} de {{ $attendees->count() }} con asistencia ·
                    <x-ui.hours :value="$attended->sum(fn($attendee) => $attendee->event->hours)" size="sm" class="text-ink" /></span>
                @if ($attendees_update)
                    <span>Actualizado <x-ui.time :value="$attendees_update" format="relative" /></span>
                @endif
            </x-slot:footer>
        @endif
    </x-ui.table>
</x-layout.app>
