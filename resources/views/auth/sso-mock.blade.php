{{-- Local development screen to simulate a University of Seville SSO callback with any UVUS --}}
<x-layout.public title="Simulador SSO de la US">
    <x-ui.card title="Simulador SSO de la Universidad de Sevilla" :level="1">
        <div class="mb-5">
            <x-ui.alert tone="notice" title="Modo de desarrollo local (US_SSO_MOCK_ENABLED)" compact>
                Introduce un identificador UVUS para simular el retorno autenticado desde el proveedor de identidad de la
                Universidad de Sevilla y verificar tanto el acceso de cuentas importadas como el rechazo de cuentas no
                autorizadas.
            </x-ui.alert>
        </div>
        <x-ui.form method="get" :action="route('sso.callback')" id="sso-mock-form" busy-label="Entrando" :summary="false">
            <x-ui.field name="uvus" label="UVUS" size="lg" icon="user-round" autocomplete="username"
                hint="Por ejemplo: profesor1, user1 o un UVUS no importado para probar el rechazo." required />
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" size="lg" icon="log-in" block>Simular acceso con este
                    UVUS</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
        <x-slot:footer>
            <a href="{{ route('login') }}"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-ink pointer-coarse:min-h-11">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />Volver al acceso</a>
        </x-slot:footer>
    </x-ui.card>
</x-layout.public>
