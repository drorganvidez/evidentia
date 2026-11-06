{{-- The three steps of new minutes; $step is the current one (1, 2 or 3) --}}
@php
    $state = fn(int $number): string => match (true) {
        $number < $step => 'done',
        $number === $step => 'current',
        default => 'upcoming',
    };
@endphp
<x-ui.timeline variant="steps" label="Pasos del acta" :columns="3">
    <x-ui.timeline.item :state="$state(1)" title="Convocatoria" />
    <x-ui.timeline.item :state="$state(2)" title="Hoja de firmas" />
    <x-ui.timeline.item :state="$state(3)" title="Redacción" />
</x-ui.timeline>
