{{-- Figure chips of the access page, rising one after another, with thousands, decimals and a tone each --}}
<section id="ui-count-chip" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-count-chip-title">
    <h2 id="ui-count-chip-title" class="text-2xl font-bold tracking-tight">Cifras</h2>

    <div class="grid gap-3">
        <span class="font-mono text-2xs text-ink-muted">delay 0 · 80 · 160 ms</span>
        <ul class="flex flex-wrap items-center gap-3">
            <li><x-ui.count-chip :value="1204" unit="h acreditadas" tone="accepted" /></li>
            <li><x-ui.count-chip :value="312" unit="evidencias aceptadas" :delay="80" /></li>
            <li><x-ui.count-chip :value="10.5" :decimals="1" unit="h por estudiante" tone="notice"
                    :delay="160" /></li>
        </ul>
    </div>
</section>
