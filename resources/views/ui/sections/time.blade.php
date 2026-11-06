{{-- Every format of the time component, then the hours figures --}}
@php
    $now = now();
@endphp
<section id="ui-time" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-time-title">
    <h2 id="ui-time-title" class="text-2xl font-bold tracking-tight">Fechas y horas</h2>

    <h3 class="text-lg font-bold tracking-tight">Fechas</h3>
    <dl class="grid grid-cols-[minmax(0,1fr)] gap-3 md:grid-cols-2 lg:grid-cols-4">
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">both</dt>
            <dd><x-ui.time :value="$now->copy()->subHours(6)" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">relative</dt>
            <dd><x-ui.time :value="$now->copy()->subDays(2)" format="relative" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">absolute</dt>
            <dd><x-ui.time :value="$now->copy()->subMonths(3)" format="absolute" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">absolute · pattern="d/m/Y"</dt>
            <dd><x-ui.time :value="$now->copy()->subMonths(3)" format="absolute" pattern="d/m/Y" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">inline</dt>
            <dd><x-ui.time :value="$now->copy()->subMinutes(45)" inline /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">short · este año</dt>
            <dd><x-ui.time :value="$now->copy()->subDay()" inline short /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">short · otro año</dt>
            <dd><x-ui.time :value="$now->copy()->subYear()" inline short /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">null</dt>
            <dd><x-ui.time :value="null" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4 md:col-span-2">
            <dt class="font-mono text-2xs text-ink-muted">absolute · patrón largo</dt>
            <dd><x-ui.time :value="$now->copy()->subWeeks(2)" format="absolute" pattern="l j \d\e F \d\e Y, H:i" :mono="false" />
            </dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4 md:col-span-2">
            <dt class="font-mono text-2xs text-ink-muted">línea meta de una tarjeta</dt>
            <dd class="flex flex-wrap items-center gap-x-2 text-sm text-ink-muted">
                <x-ui.hours :value="1.75" size="sm" />
                <span aria-hidden="true">·</span>
                <x-ui.time :value="$now->copy()->subHours(6)" inline short />
            </dd>
        </div>
    </dl>

    <h3 class="text-lg font-bold tracking-tight">Horas</h3>
    <dl class="grid grid-cols-[minmax(0,1fr)] gap-3 md:grid-cols-2 lg:grid-cols-4">
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">1.75</dt>
            <dd><x-ui.hours :value="1.75" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">4</dt>
            <dd><x-ui.hours :value="4" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">1204.5</dt>
            <dd><x-ui.hours :value="1204.5" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">1.755 · exacto en sr-only y en title</dt>
            <dd><x-ui.hours :value="1.755" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">null</dt>
            <dd><x-ui.hours :value="null" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">null · nullText</dt>
            <dd><x-ui.hours :value="null" null-text="Sin horas" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">muted · decimals=1</dt>
            <dd><x-ui.hours :value="2.5" :decimals="1" muted /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4">
            <dt class="font-mono text-2xs text-ink-muted">unit=""</dt>
            <dd><x-ui.hours :value="12" unit="" /></dd>
        </div>
        <div class="glass-2 grid gap-1 rounded-md p-4 md:col-span-2 lg:col-span-4">
            <dt class="font-mono text-2xs text-ink-muted">size · sm md lg xl</dt>
            <dd class="flex flex-wrap items-baseline gap-4">
                <x-ui.hours :value="1.75" size="sm" />
                <x-ui.hours :value="1.75" size="md" />
                <x-ui.hours :value="1.75" size="lg" />
                <x-ui.hours :value="1.75" size="xl" class="font-bold tracking-tighter" />
            </dd>
        </div>
    </dl>
</section>
