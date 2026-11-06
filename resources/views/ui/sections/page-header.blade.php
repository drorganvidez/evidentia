{{-- Page headers: the greeting with a primary action and an inline meta line, a detail with eyebrow and two actions, a bare title --}}
<section id="ui-page-header" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-page-header-title">
    <h2 id="ui-page-header-title" class="text-2xl font-bold tracking-tight">Cabecera de página</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4">
        {{-- Every example uses level 3 and an id of its own: the page keeps a single h1 and page-title is for the layout --}}
        <div class="glass-2 grid gap-2 rounded-lg pb-5" data-demo="greeting">
            <span class="px-4 pt-4 font-mono text-2xs text-ink-muted">title · lede · meta · actions</span>
            <x-ui.page-header title="Hola, Juan" :level="3" id="demo-greeting-title"
                lede="Tienes dos evidencias pendientes de revisión y un plazo que cierra esta semana.">
                <x-slot:meta>
                    <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-muted">
                        <x-ui.badge size="sm" tone="pending" :dot="false">14 días</x-ui.badge>
                        <span>para subir evidencias · cierra el <x-ui.time :value="now()->addDays(14)" format="absolute"
                                pattern="d/m · H:i" /></span>
                    </p>
                </x-slot:meta>
                <x-slot:actions>
                    <x-ui.button variant="primary" size="lg" icon="plus"
                        href="{{ route('ui.index') }}#ui-page-header" block class="md:w-auto">Nueva
                        evidencia</x-ui.button>
                </x-slot:actions>
            </x-ui.page-header>
        </div>

        <div class="glass-2 grid gap-2 rounded-lg pb-5" data-demo="detail">
            <span class="px-4 pt-4 font-mono text-2xs text-ink-muted">eyebrow · dos acciones</span>
            <x-ui.page-header title="Montaje del photocall" :level="3" id="demo-detail-title">
                <x-slot:eyebrow>
                    <x-ui.badge variant="chip" mono>EV-0033</x-ui.badge>
                    <x-ui.badge tone="pending" pulse>Pendiente de revisión</x-ui.badge>
                    <x-ui.badge variant="chip" :hue="30">Logística</x-ui.badge>
                </x-slot:eyebrow>
                <x-slot:actions>
                    <x-ui.button size="lg" icon="pencil" href="{{ route('ui.index') }}#ui-page-header" block
                        class="md:w-auto">Editar</x-ui.button>
                    <x-ui.button variant="primary" size="lg" icon="send"
                        href="{{ route('ui.index') }}#ui-page-header" block class="md:w-auto">Enviar a
                        revisión</x-ui.button>
                </x-slot:actions>
            </x-ui.page-header>
        </div>

        <div class="glass-2 grid gap-2 rounded-lg pb-5" data-demo="bare">
            <span class="px-4 pt-4 font-mono text-2xs text-ink-muted">solo título</span>
            <x-ui.page-header title="Mis reuniones" :level="3" id="demo-bare-title" />
        </div>
    </div>
</section>
