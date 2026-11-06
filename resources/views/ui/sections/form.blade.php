{{-- Forms: the error summary, the access form, a GET search and a download with the busy state --}}
@php
    $committees = [1 => 'Programa', 2 => 'Logística', 3 => 'Comunicación', 4 => 'Patrocinio'];
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
                <x-ui.field name="title" label="Título" required value="" hint="Mínimo 5 caracteres" />
                <fieldset class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-1.5">
                    <legend class="text-sm font-semibold">Duración</legend>
                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.field name="hours" label="Horas" type="number" suffix="h" inputmode="decimal"
                            min="0" step="0.25" value="1" />
                        <x-ui.field name="minutes" label="Minutos" type="number" suffix="min" inputmode="numeric"
                            min="0" max="59" step="15" value="30" />
                    </div>
                </fieldset>
                <x-ui.field name="committee" label="Comité" type="select" :options="$committees"
                    placeholder="Elige un comité" value="2" required />
                <x-ui.field name="description" label="Descripción" type="textarea" :rows="3" :maxlength="1000"
                    value="Montaje del photocall." />
                <x-ui.upload name="evidence_files[]" :url="route('ui.demo.upload')" :revert-url="route('ui.demo.revert')" :remove-url="route('ui.demo.remove', 'NAME')"
                    :files="['informe-final.pdf']" />
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
                <x-ui.field name="login_user" label="Usuario" size="lg" icon="user-round" autocomplete="username"
                    required />
                <x-ui.field name="login_password" label="Contraseña" type="password" size="lg" icon="lock"
                    autocomplete="current-password" required />
                <x-slot:actions>
                    <x-ui.button type="submit" variant="primary" size="lg" block>Entrar</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </div>

        <div class="glass-2 grid grid-cols-[minmax(0,1fr)] gap-4 rounded-lg p-5">
            <h3 class="text-lg font-bold tracking-tight">Búsqueda</h3>
            <x-ui.form method="get" :action="route('ui.index')" :summary="false" busy-label="Buscando" id="search-form">
                <x-ui.field name="search_q" label="Buscar" type="search" icon="search" placeholder="Nombre o título"
                    autocomplete="off" />
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
