{{-- Random pick of one accepted evidence per student, for the final assessment; it can run again --}}
<x-layout.app title="Aleatorizar evidencias" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Aleatorizar evidencias']]">
    <x-ui.page-header title="Aleatorizar evidencias">
        <x-slot:actions>
            <x-ui.dialog id="randomize" variant="alert" title="Aleatorizar evidencias"
                description="Se elegirá al azar una evidencia aceptada de cada alumno y se sustituirá la elección anterior."
                trigger="Aleatorizar" trigger-variant="primary" trigger-icon="shuffle">
                <x-slot:footer>
                    <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                    <form method="post" action="{{ $route }}">
                        @csrf
                        <x-ui.button variant="primary" type="submit" icon="shuffle" block>Sí, aleatorizar</x-ui.button>
                    </form>
                </x-slot:footer>
            </x-ui.dialog>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
        <x-ui.stat label="Evidencias elegidas" :value="(string) $selected" foot="una por alumno" :tone="$selected > 0 ? 'accepted' : null" />
    </div>
</x-layout.app>
