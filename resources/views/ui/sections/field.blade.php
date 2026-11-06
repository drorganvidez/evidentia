{{-- Every kind of field: icon, email, password, number pairs in a fieldset, dates, search, select, textarea and states --}}
@php
    $committees = [1 => 'Programa', 2 => 'Logística', 3 => 'Comunicación', 4 => 'Patrocinio'];
    $provinces = [
        'A Coruña',
        'Álava',
        'Albacete',
        'Alicante',
        'Almería',
        'Asturias',
        'Ávila',
        'Badajoz',
        'Barcelona',
        'Bizkaia',
        'Burgos',
        'Cáceres',
        'Cádiz',
        'Cantabria',
        'Castellón',
        'Ciudad Real',
        'Córdoba',
        'Cuenca',
        'Gipuzkoa',
        'Girona',
        'Granada',
        'Guadalajara',
        'Huelva',
        'Huesca',
        'Illes Balears',
        'Jaén',
        'La Rioja',
        'Las Palmas',
        'León',
        'Lleida',
        'Lugo',
        'Madrid',
        'Málaga',
        'Murcia',
        'Navarra',
        'Ourense',
        'Palencia',
        'Pontevedra',
        'Salamanca',
        'Santa Cruz de Tenerife',
        'Segovia',
        'Sevilla',
        'Soria',
        'Tarragona',
        'Teruel',
        'Toledo',
        'Valencia',
        'Valladolid',
        'Zamora',
        'Zaragoza',
    ];
@endphp
<section id="ui-field" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-field-title">
    <h2 id="ui-field-title" class="text-2xl font-bold tracking-tight">Campos</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-5 md:grid-cols-2 lg:grid-cols-3">
        <x-ui.field name="headline" label="Título" icon="pen-line" value="Montaje del photocall" required
            hint="Mínimo 5 caracteres" />
        <x-ui.field name="contact_email" label="Correo electrónico" type="email" icon="mail" autocomplete="email"
            placeholder="nombre@us.es" />
        <x-ui.field name="secret" label="Contraseña" type="password" autocomplete="new-password"
            hint="Mínimo 8 caracteres" />

        <fieldset class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-1.5">
            <legend class="text-sm font-semibold">Duración</legend>
            <div class="grid grid-cols-2 gap-4">
                <x-ui.field name="duration_hours" label="Horas" type="number" suffix="h" inputmode="decimal"
                    min="0" step="0.25" value="1" />
                <x-ui.field name="duration_minutes" label="Minutos" type="number" suffix="min" inputmode="numeric"
                    min="0" max="59" step="15" value="30" />
            </div>
        </fieldset>

        <fieldset class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-1.5">
            <legend class="text-sm font-semibold">Fecha y hora</legend>
            <div class="grid grid-cols-2 gap-4">
                <x-ui.field name="event_date" label="Día" type="date" value="2026-11-06" />
                <x-ui.field name="event_time" label="Hora" type="time" value="18:30" />
            </div>
        </fieldset>

        <x-ui.field name="q" label="Buscar" type="search" icon="search" hide-label placeholder="Buscar"
            autocomplete="off" class="self-end">
            <x-slot:trailing>
                <kbd class="rounded-xs border border-rule px-1.5 font-mono text-xs text-ink-muted">/</kbd>
            </x-slot:trailing>
        </x-ui.field>

        <x-ui.field name="committee_id" label="Comité" type="select" :options="$committees" placeholder="Elige un comité"
            required />
        <x-ui.field name="session_state" label="Estado" type="select" :options="['open' => 'Abierta', 'closed' => 'Cerrada']" value="open" />
        <x-ui.field name="province" label="Provincia" type="select" :options="array_combine($provinces, $provinces)" placeholder="Elige una provincia"
            icon="map-pin" />
        <x-ui.field name="target_committee" label="Comité de destino" type="select" :options="$committees"
            placeholder="Elige un comité" error="Elige un comité de destino" />
        <x-ui.field name="locked_committee" label="Comité asignado" type="select" :options="$committees" value="3"
            disabled />
        <x-ui.field name="audience" label="Dirigido a" type="select" :options="[
            ['value' => '', 'label' => 'Todos'],
            ['value' => 'students', 'label' => 'Estudiantes'],
            ['value' => 'lecturers', 'label' => 'Profesorado', 'disabled' => true],
            ['value' => 'secretaries', 'label' => 'Secretaría'],
        ]" />
        <x-ui.field name="reference" label="Identificador" value="EV-0033" readonly prefix="#"
            hint="Lo asigna la aplicación" />
        <x-ui.field name="locked_field" label="Campo deshabilitado" value="Sin cambios" disabled />
        <x-ui.field name="amount" label="Importe" type="number" prefix="EUR" inputmode="decimal" value="-3"
            error="El importe no puede ser negativo" />
        <x-ui.field name="access_user" label="Usuario" size="lg" icon="user-round" autocomplete="username"
            hint="Tamaño grande para el acceso y la firma" />
        <x-ui.field name="notes" label="Observaciones" type="textarea" :rows="4" :maxlength="300"
            placeholder="Qué se hizo y con quién" value="Montaje y recogida del photocall con el equipo de logística."
            class="md:col-span-2 lg:col-span-3" />
    </div>
</section>
