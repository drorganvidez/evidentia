{{--
    Profile of the signed-in person: the summary beside the three forms of the old tabs (personal data, the summary of
    the work in the course, which the lecturer does not have, and the password). The forms share one error bag, so
    each field shows its own error and no form prints a summary of the others.
--}}
@php
    $user = Auth::user();
@endphp
<x-layout.app title="Mi perfil" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Mi perfil']]">
    <x-ui.page-header title="Mi perfil">
        <x-slot:actions>
            <x-ui.button variant="ghost" icon="bell" :href="route('notifications.preferences')">Preferencias de avisos</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Datos personales" :level="2" id="data-title">
                <x-ui.form :action="$route_upload_info" id="data-form" :summary="false" busy-label="Guardando">
                    <x-ui.field name="username" label="UVUS" :value="$user->username" disabled />
                    <div class="grid items-start gap-5 md:grid-cols-2">
                        <x-ui.field name="name" label="Nombre" :value="$user->name" autocomplete="given-name"
                            required />
                        <x-ui.field name="surname" label="Apellidos" :value="$user->surname" autocomplete="family-name"
                            required />
                    </div>
                    <x-ui.field name="email" label="Correo electrónico" type="email" :value="$user->email"
                        autocomplete="email" required />
                    <x-slot:actions>
                        <x-ui.button type="submit" variant="primary" icon="save">Guardar los datos</x-ui.button>
                    </x-slot:actions>
                </x-ui.form>
            </x-ui.card>

            @unless ($user->hasRole('LECTURE'))
                <x-ui.card title="Resumen del trabajo" :level="2" id="biography-title">
                    <x-ui.form :action="$route_upload_biography" id="biography-form" :summary="false" busy-label="Guardando">
                        <x-ui.field name="biography" label="Resumen del trabajo en las jornadas" type="textarea"
                            :rows="8" :maxlength="20000" :value="\App\View\PlainText::fromHtml($user->biography)" hint="Al menos 10 caracteres"
                            required />
                        <x-slot:actions>
                            <x-ui.button type="submit" variant="primary" icon="save">Guardar el resumen</x-ui.button>
                        </x-slot:actions>
                    </x-ui.form>
                </x-ui.card>
            @endunless

            <x-ui.card title="Contraseña" :level="2" id="password-title">
                <x-ui.form :action="$route_upload_pass" id="password-form" :summary="false" busy-label="Guardando">
                    <x-ui.field name="current_password" label="Contraseña actual" type="password"
                        autocomplete="current-password" required />
                    <div class="grid items-start gap-5 md:grid-cols-2">
                        <x-ui.field name="password" label="Nueva contraseña" type="password" autocomplete="new-password"
                            required />
                        <x-ui.field name="password_confirmation" label="Repite la contraseña" type="password"
                            autocomplete="new-password" required />
                    </div>
                    <x-ui.password-rules for="field-password" confirm="field-password-confirmation" />
                    <x-slot:actions>
                        <x-ui.button type="submit" variant="primary" icon="key-round">Cambiar la
                            contraseña</x-ui.button>
                    </x-slot:actions>
                </x-ui.form>
            </x-ui.card>
        </div>

        <aside aria-label="Resumen" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            @include('profile.partials.summary', ['user' => $user, 'hours' => $hours, 'own' => true])
        </aside>
    </div>
</x-layout.app>
