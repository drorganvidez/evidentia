{{-- Timelines: the steps of an evidence with an incident, an accepted one, a wizard with links and the log of an evidence --}}
@php
    $now = now();
    $link = route('ui.index') . '#ui-timeline';
@endphp
<section id="ui-timeline" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-timeline-title">
    <h2 id="ui-timeline-title" class="text-2xl font-bold tracking-tight">Recorridos e historial</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Recorrido con incidencia</h3>
        <x-ui.timeline variant="steps" label="Recorrido de la evidencia rechazada" :columns="4">
            <x-ui.timeline.item state="done" title="Borrador" :at="$now->copy()->subDays(5)" />
            <x-ui.timeline.item state="done" title="Enviada" :at="$now->copy()->subDays(4)" />
            <x-ui.timeline.item state="error" title="Rechazada" :at="$now->copy()->subDays(2)" />
            <x-ui.timeline.item state="upcoming" title="Aceptada" hint="Pendiente de corregir" />
        </x-ui.timeline>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Recorrido completado</h3>
        <x-ui.timeline variant="steps" label="Recorrido de la evidencia aceptada" :columns="3">
            <x-ui.timeline.item state="done" title="Borrador" :at="$now->copy()->subDays(9)" />
            <x-ui.timeline.item state="done" title="Enviada" :at="$now->copy()->subDays(8)" />
            <x-ui.timeline.item state="current" title="Aceptada" :at="$now->copy()->subDays(6)" />
        </x-ui.timeline>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Asistente de tres pasos con enlaces</h3>
        <x-ui.timeline variant="steps" label="Pasos del acta">
            <x-ui.timeline.item state="done" title="Datos de la reunión" :href="$link" hint="Completado" />
            <x-ui.timeline.item state="current" title="Acuerdos" hint="Paso 2 de 3" />
            <x-ui.timeline.item state="upcoming" title="Firma" hint="Paso 3 de 3" />
        </x-ui.timeline>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Historial</h3>
        <x-ui.card padding="sm" aria-label="Historial de la evidencia">
            <x-ui.timeline label="Historial de la evidencia">
                <x-ui.timeline.item tone="rejected" title="Rechazada" :at="$now->copy()->subDays(2)">
                    por <a href="{{ $link }}" class="font-semibold text-brand-ink">Carlos Navarro</a>
                </x-ui.timeline.item>
                <x-ui.timeline.item tone="pending" title="Enviada" :at="$now->copy()->subDays(4)">· versión 2</x-ui.timeline.item>
                <x-ui.timeline.item tone="draft" title="Editada" :at="$now->copy()->subDays(4)->subHours(2)">
                    por <a href="{{ $link }}" class="font-semibold text-brand-ink">Lucía Pérez</a> · versión 2
                </x-ui.timeline.item>
                <x-ui.timeline.item tone="draft" title="Creada" :at="$now->copy()->subDays(5)">
                    por <a href="{{ $link }}" class="font-semibold text-brand-ink">Lucía Pérez</a>
                </x-ui.timeline.item>
            </x-ui.timeline>
        </x-ui.card>
    </div>
</section>
