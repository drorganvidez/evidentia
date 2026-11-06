{{-- Groups of checkboxes: the roles with the dependent committee and the blocks of an export --}}
@php
    $roles = [
        ['value' => 'STUDENT', 'label' => 'Estudiante', 'hint' => 'Sube evidencias y registra horas'],
        ['value' => 'COORDINATOR', 'label' => 'Coordinador', 'hint' => 'Valida las evidencias de su comité'],
        ['value' => 'SECRETARY', 'label' => 'Secretario', 'hint' => 'Convoca reuniones y firma actas'],
        ['value' => 'REGISTER_COORDINATOR', 'label' => 'Coordinador de registro', 'hint' => null],
    ];
    $committees = [1 => 'Programa', 2 => 'Logística', 3 => 'Comunicación', 4 => 'Patrocinio'];
    $blocks = [
        ['value' => 'evidences', 'label' => 'Evidencias'],
        ['value' => 'hours', 'label' => 'Horas'],
        ['value' => 'meetings', 'label' => 'Reuniones'],
        ['value' => 'attendees', 'label' => 'Asistencias'],
    ];
@endphp
<section id="ui-checkbox-group" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-checkbox-group-title">
    <h2 id="ui-checkbox-group-title" class="text-2xl font-bold tracking-tight">Grupos de casillas</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2">
        <x-ui.checkbox-group name="roles" legend="Roles" :options="$roles" :selected="['STUDENT']" :columns="2" required
            hint="El comité solo se pide a coordinadores y secretarios">
            <x-slot:dependent>
                <div x-show="hasAny('COORDINATOR', 'SECRETARY')" x-cloak class="glass-2 rounded-lg p-4">
                    <x-ui.field name="role_committee" label="Comité" type="select" :options="$committees"
                        placeholder="Elige un comité" />
                </div>
            </x-slot:dependent>
        </x-ui.checkbox-group>

        <x-ui.checkbox-group name="export" legend="Qué exportar" :options="$blocks" :selected="['evidences', 'hours']" :columns="2"
            error="Elige al menos un bloque" />
    </div>
</section>
