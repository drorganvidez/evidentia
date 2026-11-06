{{--
    Access page: the claim, the figures of the edition and the scene of the platform beside the login form. The field
    keeps the name email of the login of Laravel; a failed login shows one message of its own instead of the
    untranslated one of the framework.
--}}
@php
    $failed = $errors->has('email') || $errors->has('password');
    $throttled = $failed && $errors->first('email') !== __('auth.failed') && $errors->first('email') !== '';
    $message = $throttled
        ? 'Demasiados intentos, espera un minuto y vuelve a probar'
        : 'El correo o la contraseña no son correctos';
@endphp
<x-layout.public title="Acceso" wide :subtitle="'Jornadas InnoSoft ' . $edition">
    <x-slot:aside class="content-center lg:pr-8">
        <x-ui.badge variant="chip" icon="calendar" :hue="270" class="justify-self-start">Edición
            {{ $edition }}</x-ui.badge>
        <h1
            class="max-w-[14ch] text-[clamp(2rem,9vw,2.625rem)] leading-none font-extrabold tracking-tightest text-ink lg:text-6xl">
            Lo que haces en las jornadas,
            <em
                class="bg-linear-to-r from-aurora-600 via-cyan-600 via-60% to-rose-600 bg-clip-text not-italic text-transparent dark:from-aurora-400 dark:via-cyan-400 dark:to-rose-400">cuenta.</em>
        </h1>
        @if ($figures['evidences'] > 0)
            <ul class="flex flex-wrap gap-3 max-lg:text-sm" aria-label="Cifras de la edición">
                <li><x-ui.count-chip :value="$figures['hours']" :decimals="$figures['hours'] == floor($figures['hours']) ? 0 : 1" unit="h acreditadas" /></li>
                <li><x-ui.count-chip :value="$figures['evidences']" :unit="$figures['evidences'] === 1 ? 'evidencia aceptada' : 'evidencias aceptadas'" tone="accepted" :delay="80" /></li>
                <li><x-ui.count-chip :value="$figures['students']" :unit="$figures['students'] === 1 ? 'estudiante' : 'estudiantes'" tone="notice" :delay="160" />
                </li>
            </ul>
        @endif
        <x-ui.scene class="lg:mt-2" />
    </x-slot:aside>

    <x-ui.card title="Iniciar sesión" :level="2">
        <x-ui.form :action="route('login')" busy-label="Entrando" id="login-form" :summary="false">
            @if ($failed)
                <x-ui.alert tone="rejected" role="alert" :title="$message" compact />
            @endif
            <x-ui.field name="email" label="Correo electrónico" type="email" size="lg" icon="mail"
                autocomplete="username" :error="$failed ? $message : null" required />
            <x-ui.field name="password" label="Contraseña" type="password" size="lg" icon="lock"
                autocomplete="current-password" required />
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
                <x-ui.choice type="switch" name="remember" label="Recordarme" />
                <a href="{{ route('password.custom_reset') }}"
                    class="text-sm font-semibold text-brand-ink pointer-coarse:inline-flex pointer-coarse:min-h-11 pointer-coarse:items-center">He
                    olvidado la contraseña</a>
            </div>
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" size="lg" icon="log-in" block>Entrar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
        <x-slot:footer>
            <p class="flex flex-wrap justify-between gap-x-4 gap-y-2 text-sm">
                <a href="{{ route('password.custom_reset') }}"
                    class="font-semibold text-brand-ink pointer-coarse:inline-flex pointer-coarse:min-h-11 pointer-coarse:items-center">Es
                    mi primer acceso</a>
                <a href="https://t.me/evidentia_sat"
                    class="font-semibold text-brand-ink pointer-coarse:inline-flex pointer-coarse:min-h-11 pointer-coarse:items-center"
                    rel="noopener">Asistencia técnica en Telegram</a>
            </p>
        </x-slot:footer>
    </x-ui.card>
</x-layout.public>
