{{-- The search form of the topbar: hidden on the phone by default, always visible inside the drawer --}}
<section id="ui-search" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-search-title">
    <h2 id="ui-search-title" class="text-2xl font-bold tracking-tight">Buscador</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
        <div class="glass-2 grid content-start gap-2 rounded-lg p-4" data-demo="topbar">
            <span class="font-mono text-2xs text-ink-muted">topbar · hidden md:block · atajo /</span>
            <x-ui.search :action="route('ui.index')" id="field-q-topbar" />
        </div>
        <div class="glass-2 grid content-start gap-2 rounded-lg p-4" data-demo="drawer">
            <span class="font-mono text-2xs text-ink-muted">cajón · hideOnPhone=false · value</span>
            <x-ui.search :action="route('ui.index')" id="field-q-drawer" :hide-on-phone="false" value="photocall"
                placeholder="Buscar reuniones" shortcut="." />
        </div>
    </div>
</section>
