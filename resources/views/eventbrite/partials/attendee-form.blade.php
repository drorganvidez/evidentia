{{--
    Form of an attendance recorded by hand: the person, the event and whether they attended.
    @include('eventbrite.partials.attendee-form', ['action' => ..., 'attendee' => $attendee|null, 'event' => id|null, 'submit' => 'Crear asistencia'])
--}}
@php
    $text = fn($html) => trim(html_entity_decode(strip_tags(\Html::clean((string) $html)), ENT_QUOTES | ENT_HTML5));
    $people = $users
        ->map(fn($user) => ['value' => (string) $user->id, 'label' => trim($user->surname . ', ' . $user->name, ', ')])
        ->values()
        ->all();
    $eventOptions = $events
        ->map(
            fn($item) => [
                'value' => (string) $item->id,
                'label' =>
                    ($text($item->name) ?: 'Sin nombre') .
                    ($item->start_datetime
                        ? ' · ' . \Carbon\Carbon::parse($item->start_datetime)->format('d/m/Y H:i')
                        : ''),
            ],
        )
        ->values()
        ->all();
@endphp
<x-ui.form :action="$action" id="attendee-form" :fields="['user_id' => 'Estudiante', 'event_id' => 'Evento', 'status' => 'Asistencia']">
    <x-ui.field name="user_id" label="Estudiante" type="select" :options="$people" :value="$attendee?->user_id" required />
    <x-ui.field name="event_id" label="Evento" type="select" :options="$eventOptions" :value="$attendee?->event_id ?? $event" required />
    <x-ui.field name="status" label="Asistencia" type="select" :options="[
        ['value' => 'Checked In', 'label' => 'Asistido'],
        ['value' => 'Not Attending', 'label' => 'No asistido'],
    ]" :value="$attendee?->status" required
        class="md:max-w-64" />
    <x-slot:actions>
        <x-ui.button type="submit" variant="primary" icon="save">{{ $submit }}</x-ui.button>
        <x-ui.button variant="ghost" :href="route('registercoordinator.attendee.list')">Cancelar</x-ui.button>
    </x-slot:actions>
</x-ui.form>
