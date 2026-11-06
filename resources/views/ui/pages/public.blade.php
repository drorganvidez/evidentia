{{--
    The public layout in its three uses: the access page (wide, with the claim, the figures of the edition and the scene of
    the platform), the public signature of a meeting and the not-found pattern. One page so the catalogue shows them
    together; the real screens render one each. Served at /_ui/public with the light entry (public.js).
--}}
@php
    $here = route('ui.page', 'public');
    // The forms post to the sink of the catalogue, which comes back here with a notice
    $submit = route('ui.demo.submit');
    $home = route('ui.index');
    $now = now();
@endphp
<x-layout.public title="Acceso" wide subtitle="Jornadas InnoSoft 2026" version="4.0.0">
    <x-slot:aside class="content-center lg:pr-8">
        <x-ui.badge variant="chip" icon="calendar" :hue="270" class="justify-self-start">Edición 2026</x-ui.badge>
        <h1
            class="max-w-[14ch] text-[clamp(2rem,9vw,2.625rem)] leading-none font-extrabold tracking-tightest text-ink lg:text-6xl">
            Lo que haces en las jornadas,
            <em
                class="bg-linear-to-r from-aurora-600 via-cyan-600 via-60% to-rose-600 bg-clip-text not-italic text-transparent dark:from-aurora-400 dark:via-cyan-400 dark:to-rose-400">cuenta.</em>
        </h1>
        <ul class="flex flex-wrap gap-3 max-lg:text-sm" aria-label="Cifras de la edición">
            <li><x-ui.count-chip :value="1204" unit="h acreditadas" /></li>
            <li><x-ui.count-chip :value="312" unit="evidencias aceptadas" tone="accepted" :delay="80" /></li>
            <li><x-ui.count-chip :value="10.5" :decimals="1" unit="h por estudiante" tone="notice"
                    :delay="160" />
            </li>
        </ul>
        <x-ui.scene class="lg:mt-2" />
    </x-slot:aside>

    <x-ui.card title="Iniciar sesión" :level="2">
        <x-ui.form :action="$submit" busy-label="Entrando" id="access-form" :fields="['user' => 'Usuario', 'password' => 'Contraseña']">
            <x-ui.field name="user" label="Usuario" size="lg" icon="user-round" autocomplete="username"
                required />
            <x-ui.field name="password" label="Contraseña" type="password" size="lg" icon="lock"
                autocomplete="current-password" required />
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
                <x-ui.choice type="switch" name="remember" label="Recordarme" />
                <a href="{{ $here }}#recuperar"
                    class="text-sm font-semibold text-brand-ink pointer-coarse:inline-flex pointer-coarse:min-h-11 pointer-coarse:items-center">He
                    olvidado la
                    contraseña</a>
            </div>
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" size="lg" icon="log-in" block>Entrar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>

    {{-- The one-column uses of the layout, under the access --}}
    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:col-span-2 lg:mt-6">
        <h2
            class="flex items-center gap-4 text-xs font-bold tracking-widest text-ink uppercase before:h-px before:flex-1 before:bg-rule after:h-px after:flex-1 after:bg-rule">
            Firma pública y errores</h2>
        <div class="grid grid-cols-[minmax(0,1fr)] gap-8 md:grid-cols-2 md:items-start">
            <x-ui.card title="Firma de asistencia" :level="3" id="sign">
                <div class="grid grid-cols-[minmax(0,1fr)] gap-5">
                    <x-ui.dl dense>
                        <x-ui.dl.item term="Reunión">Reunión ordinaria 3 · Comité de Programa</x-ui.dl.item>
                        <x-ui.dl.item term="Fecha">
                            <x-ui.time :value="$now->copy()->addDays(3)->setTime(17, 0)" format="absolute" pattern="l j \d\e F, H:i"
                                :mono="false" />
                        </x-ui.dl.item>
                        <x-ui.dl.item term="Lugar">Aula A2.11, ETSII</x-ui.dl.item>
                    </x-ui.dl>
                    <x-ui.form :action="$submit" id="sign-form" busy-label="Firmando" :fields="['uvus' => 'UVUS', 'name' => 'Nombre y apellidos']">
                        <x-ui.field name="uvus" label="UVUS" autocomplete="username" required />
                        <x-ui.field name="name" label="Nombre y apellidos" autocomplete="name" required />
                        <x-slot:actions>
                            <x-ui.button type="submit" variant="primary" icon="file-signature"
                                block>Firmar</x-ui.button>
                        </x-slot:actions>
                    </x-ui.form>
                </div>
            </x-ui.card>
            <x-ui.card as="div">
                <x-ui.empty code="404" title="No encontramos esa página" text="Puede que el enlace haya caducado."
                    :level="3">
                    <x-slot:actions>
                        <x-ui.button variant="primary" icon="arrow-left" :href="$home">Volver al
                            inicio</x-ui.button>
                    </x-slot:actions>
                </x-ui.empty>
            </x-ui.card>
        </div>
    </div>
</x-layout.public>
