{{-- Asks for the email that receives the link to set a new password: also the first access of a new account --}}
<x-layout.public title="Restablecer la contraseña">
    <x-ui.card title="Restablecer la contraseña" :level="1">
        <x-ui.form :action="route('password.reset_p')" id="reset-form" busy-label="Enviando" :fields="['email' => 'Correo electrónico']">
            <x-ui.field name="email" label="Correo electrónico" type="email" size="lg" icon="mail"
                autocomplete="username" required />
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" size="lg" icon="send" block>Enviar el
                    enlace</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
        <x-slot:footer>
            <a href="{{ route('login') }}"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-ink pointer-coarse:min-h-11">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />Volver al acceso</a>
        </x-slot:footer>
    </x-ui.card>
</x-layout.public>
