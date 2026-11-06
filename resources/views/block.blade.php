{{-- Notice for a blocked account: the only way out is to sign out --}}
<x-layout.public title="Cuenta bloqueada">
    <x-ui.card as="div">
        <x-ui.empty icon="lock" :level="1" :title="Auth::user()->name . ', tu cuenta está bloqueada'"
            text="Habla con tu profesor o con la presidencia de las jornadas.">
            <x-slot:actions>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button type="submit" variant="primary" icon="log-out">Cerrar sesión</x-ui.button>
                </form>
            </x-slot:actions>
        </x-ui.empty>
    </x-ui.card>
</x-layout.public>
