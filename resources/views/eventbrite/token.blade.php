{{-- Token of the Eventbrite API that loads the events and their attendances --}}
<x-layout.app title="Eventbrite" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Eventbrite']]">
    <x-ui.page-header title="Eventbrite">
        <x-slot:actions>
            <x-ui.button variant="ghost" icon="external-link" href="https://www.eventbrite.com/platform/api-keys"
                target="_blank" rel="noopener noreferrer">Obtener el token</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card title="Token de la API" :level="2" class="max-w-2xl">
        <x-ui.form :action="$route" id="token-form" :fields="['token' => 'Token']" busy-label="Validando">
            <x-ui.field name="token" label="Token" :value="$token" required autocomplete="off" spellcheck="false"
                icon="key-round" />
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="shield-check">Validar y guardar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.app>
