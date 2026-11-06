{{-- The skip link and the landmarks every page of the new UI exposes --}}
@php
    $landmarks = [
        ['skip-link', 'Primer elemento del body, visible al recibir el foco, z-50'],
        ['header', 'Marca, buscador, avisos y tema'],
        ['nav[aria-label]', 'Una navegación principal visible por tramo: sidebar, raíl o barra inferior'],
        ['main#main[tabindex="-1"]', 'Destino del skip link, con el único h1 de la página'],
        ['footer', 'Pie con la versión y los enlaces de ayuda'],
    ];
@endphp
<section id="ui-accessibility" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-accessibility-title">
    <h2 id="ui-accessibility-title" class="text-2xl font-bold tracking-tight" tabindex="-1">Accesibilidad</h2>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="glass-2 grid gap-3 rounded-lg p-5">
            <h3 class="text-lg font-bold tracking-tight">Enlace para saltar</h3>
            <p class="text-sm text-ink-muted">Pulsa Tab para ver el enlace; Intro lleva el foco al título de esta
                sección.</p>
            <p>
                <x-ui.skip-link target="#ui-accessibility-title">Saltar al título de la sección</x-ui.skip-link>
                <code class="font-mono text-xs text-ink-muted">&lt;x-ui.skip-link
                    target="#ui-accessibility-title"&gt;</code>
            </p>
        </div>

        <div class="glass-2 grid gap-3 rounded-lg p-5">
            <h3 class="text-lg font-bold tracking-tight">Landmarks</h3>
            <dl class="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-[auto_minmax(0,1fr)]">
                @foreach ($landmarks as [$selector, $role])
                    <dt class="font-mono text-xs text-ink-muted">{{ $selector }}</dt>
                    <dd>{{ $role }}</dd>
                @endforeach
            </dl>
        </div>
    </div>
</section>
