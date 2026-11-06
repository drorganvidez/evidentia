{{-- Notices: one button per tone dispatches ui-notice, another writes to the status region; the stack itself is fixed --}}
<section id="ui-notices" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-notices-title">
    <h2 id="ui-notices-title" class="text-2xl font-bold tracking-tight">Avisos</h2>

    <div class="grid gap-3">
        <span class="font-mono text-2xs text-ink-muted">$dispatch('ui-notice', { tone, text }) · accepted y notice se
            cierran a los
            8 s · rejected y pending permanecen</span>
        <div x-data class="flex flex-wrap items-center gap-3">
            <x-ui.button icon="circle-check"
                x-on:click="$dispatch('ui-notice', { tone: 'accepted', text: 'Evidencia guardada' })">Aviso de
                éxito</x-ui.button>
            <x-ui.button icon="circle-x"
                x-on:click="$dispatch('ui-notice', { tone: 'rejected', text: 'No se ha podido guardar la evidencia' })">Aviso
                de error</x-ui.button>
            <x-ui.button icon="triangle-alert"
                x-on:click="$dispatch('ui-notice', { tone: 'pending', text: 'El plazo de subida cierra en dos horas' })">Aviso
                de atención</x-ui.button>
            <x-ui.button icon="info"
                x-on:click="$dispatch('ui-notice', { tone: 'notice', text: 'Hay una nueva versión de Evidentia' })">Aviso
                informativo</x-ui.button>
            <x-ui.button variant="ghost" icon="megaphone"
                x-on:click="$store.notices.announce('Anuncio de prueba')">Anunciar en la región de estado</x-ui.button>
        </div>
    </div>

    {{-- The page needs one stack; the index sets $hasGlobalNotices when the layout already renders it --}}
    @unless ($hasGlobalNotices ?? false)
        <x-ui.notices :messages="[]" />
    @endunless
</section>
