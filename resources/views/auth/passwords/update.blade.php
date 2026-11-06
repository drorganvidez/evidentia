{{-- The new password of the link received by email; the token travels in the action, as the route expects --}}
<x-layout.public title="Nueva contraseña">
    <x-ui.card title="Nueva contraseña" :level="1">
        <x-ui.form :action="route('password.update_p', ['token' => $token])" id="update-form" busy-label="Guardando" :fields="['password' => 'Nueva contraseña', 'password_confirmation' => 'Repite la contraseña']">
            <x-ui.field name="password" label="Nueva contraseña" type="password" size="lg" icon="lock"
                autocomplete="new-password" required />
            <x-ui.field name="password_confirmation" label="Repite la contraseña" type="password" size="lg"
                icon="lock" autocomplete="new-password" required />
            <x-ui.password-rules for="field-password" confirm="field-password-confirmation" />
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" size="lg" icon="key-round" block>Guardar la
                    contraseña</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.public>
