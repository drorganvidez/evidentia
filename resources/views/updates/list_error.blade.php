{{-- The API of GitHub did not answer (its daily quota or the network): the way to the repository instead --}}
<x-layout.app title="Actualizaciones" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Actualizaciones']]">
    <x-ui.page-header title="Actualizaciones" />

    <x-ui.card as="div">
        <x-ui.empty code="403" title="GitHub no responde ahora" text="Vuelve a intentarlo más tarde."
            :level="2">
            <x-slot:actions>
                <x-ui.button variant="primary" icon="github" href="https://github.com/drorganvidez/evidentia">Ver el
                    proyecto en GitHub</x-ui.button>
            </x-slot:actions>
        </x-ui.empty>
    </x-ui.card>
</x-layout.app>
