{{-- Deadlines: open with the consumed bar, urgent, closed, without a deadline, inline under a title and linked --}}
@php
    $now = now();
@endphp
<section id="ui-deadline" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-deadline-title">
    <h2 id="ui-deadline-title" class="text-2xl font-bold tracking-tight">Plazos</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div class="grid content-start gap-2" data-demo="open">
            <span class="font-mono text-2xs text-ink-muted">abierto · opensAt · closesAt</span>
            <x-ui.deadline label="Subida de evidencias" :opens-at="$now->copy()->subDays(30)" :closes-at="$now->copy()->addDays(14)" />
        </div>
        <div class="grid content-start gap-2" data-demo="urgent">
            <span class="font-mono text-2xs text-ink-muted">urgente · menos de warnDays</span>
            <x-ui.deadline label="Solicitud de reuniones" :opens-at="$now->copy()->subDays(20)" :closes-at="$now->copy()->addDays(2)->addHours(6)" />
        </div>
        <div class="grid content-start gap-2" data-demo="closed">
            <span class="font-mono text-2xs text-ink-muted">cerrado</span>
            <x-ui.deadline label="Registro de asistencias" :opens-at="$now->copy()->subDays(40)" :closes-at="$now->copy()->subDays(3)" />
        </div>
        <div class="grid content-start gap-2" data-demo="none">
            <span class="font-mono text-2xs text-ink-muted">sin plazo</span>
            <x-ui.deadline label="Bonificaciones" />
        </div>
        <div class="grid content-start gap-2" data-demo="linked">
            <span class="font-mono text-2xs text-ink-muted">href · enlaza a la vista afectada</span>
            <x-ui.deadline label="Subida de evidencias" :closes-at="$now->copy()->addDays(9)" href="{{ route('ui.index') }}#ui-deadline" />
        </div>
        <div class="glass-2 grid content-start gap-2 rounded-lg p-4" data-demo="inline">
            <span class="font-mono text-2xs text-ink-muted">variant="inline" · verb · bajo el título en móvil</span>
            <x-ui.deadline label="Subida de evidencias" variant="inline" verb="para subir evidencias"
                :closes-at="$now->copy()->addDays(14)" />
            <x-ui.deadline label="Registro de asistencias" variant="inline" :closes-at="$now->copy()->subDays(3)" />
        </div>
    </div>
</section>
