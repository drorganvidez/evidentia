{{-- Forms: the error summary, the access form, a GET search and a download with the busy state --}}
@php
    $inputClasses =
        'h-10.5 w-full min-w-0 rounded-md border border-field-edge bg-field px-4 text-control text-ink outline-none transition focus:border-aurora-500 focus:ring-4 focus:ring-aurora-500/18';
    $demoErrors = new Illuminate\Support\ViewErrorBag()->put(
        'default',
        new Illuminate\Support\MessageBag([
            'title' => ['El título es obligatorio'],
            'description' => ['La descripción necesita al menos 20 caracteres'],
        ]),
    );
    $previousErrors = View::shared('errors');
    View::share('errors', $demoErrors);
@endphp
<section id="ui-form" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-form-title">
    <h2 id="ui-form-title" class="text-2xl font-bold tracking-tight">Formularios</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2">
        <div class="glass-2 grid grid-cols-[minmax(0,1fr)] gap-4 rounded-lg p-5">
            <h3 class="text-lg font-bold tracking-tight">Resumen de errores</h3>
            <x-ui.form :action="route('ui.index')" files id="evidence-form" :fields="['title' => 'Título', 'description' => 'Descripción']">
                <div class="grid gap-1.5">
                    <label for="{{ ui_field_id('title') }}" class="text-sm font-semibold">Título</label>
                    <input id="{{ ui_field_id('title') }}" name="title" type="text" value="Montaje del photocall"
                        aria-invalid="true" class="{{ $inputClasses }}">
                </div>
                <div class="grid gap-1.5">
                    <label for="{{ ui_field_id('description') }}" class="text-sm font-semibold">Descripción</label>
                    <textarea id="{{ ui_field_id('description') }}" name="description" rows="3" aria-invalid="true"
                        class="{{ $inputClasses }} h-auto py-2">Montaje y recogida del photocall.</textarea>
                </div>
                <x-slot:actions>
                    <x-ui.button type="submit" variant="primary">Guardar</x-ui.button>
                    <x-ui.button href="{{ route('ui.index') }}#ui-form">Cancelar</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </div>
        @php
            View::share('errors', $previousErrors ?? new Illuminate\Support\ViewErrorBag());
        @endphp

        <div class="glass-2 grid grid-cols-[minmax(0,1fr)] gap-4 rounded-lg p-5">
            <h3 class="text-lg font-bold tracking-tight">Acceso</h3>
            <x-ui.form :action="route('ui.index')" busy-label="Entrando" id="access-form">
                <div class="grid gap-1.5">
                    <label for="{{ ui_field_id('login_user') }}" class="text-sm font-semibold">Usuario</label>
                    <input id="{{ ui_field_id('login_user') }}" name="login_user" type="text" autocomplete="username"
                        required class="{{ $inputClasses }}">
                </div>
                <div class="grid gap-1.5">
                    <label for="{{ ui_field_id('login_password') }}" class="text-sm font-semibold">Contraseña</label>
                    <input id="{{ ui_field_id('login_password') }}" name="login_password" type="password"
                        autocomplete="current-password" required class="{{ $inputClasses }}">
                </div>
                <x-slot:actions>
                    <x-ui.button type="submit" variant="primary" block>Entrar</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </div>

        <div class="glass-2 grid grid-cols-[minmax(0,1fr)] gap-4 rounded-lg p-5">
            <h3 class="text-lg font-bold tracking-tight">Búsqueda</h3>
            <x-ui.form method="get" :action="route('ui.index')" :summary="false" busy-label="Buscando" id="search-form">
                <div class="grid gap-1.5">
                    <label for="{{ ui_field_id('search_q') }}" class="text-sm font-semibold">Buscar</label>
                    <input id="{{ ui_field_id('search_q') }}" name="search_q" type="search"
                        class="{{ $inputClasses }}">
                </div>
                <x-slot:actions>
                    <x-ui.button type="submit" icon="search">Buscar</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </div>

        <div class="glass-2 grid grid-cols-[minmax(0,1fr)] gap-4 rounded-lg p-5">
            <h3 class="text-lg font-bold tracking-tight">Descarga con estado ocupado</h3>
            <x-ui.form method="get" :action="route('ui.index')" :summary="false" download busy-label="Preparando"
                id="export-form">
                <input type="hidden" name="export" value="csv">
                <p class="text-sm text-ink-muted">El botón queda ocupado y se libera a los ocho segundos.</p>
                <x-slot:actions>
                    <x-ui.button type="submit" variant="primary" icon="download">Descargar CSV</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </div>
    </div>
</section>
