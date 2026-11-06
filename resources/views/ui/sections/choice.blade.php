{{-- Checkboxes, radios and switches: plain, ticked, with hint, disabled and with an error --}}
<section id="ui-choice" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-choice-title">
    <h2 id="ui-choice-title" class="text-2xl font-bold tracking-tight">Casillas y conmutadores</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div class="grid grid-cols-[minmax(0,1fr)] gap-2">
            <h3 class="text-lg font-bold tracking-tight">Casillas</h3>
            <x-ui.choice name="remember" label="Recordarme" hint="Durante 30 días en este dispositivo" />
            <x-ui.choice name="notify" label="Avisarme por correo" :checked="true" />
            <x-ui.choice name="locked_choice" label="Opción deshabilitada" disabled />
            <x-ui.choice name="terms" label="He leído las condiciones" error="Debes aceptar las condiciones" />
        </div>

        <fieldset class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-2">
            <legend class="text-lg font-bold tracking-tight">Participación</legend>
            <x-ui.choice type="radio" name="participation" value="onsite" label="Presencial" :checked="true"
                hint="En el aula o en el lugar del evento" />
            <x-ui.choice type="radio" name="participation" value="online" label="En línea" hint="Por videollamada" />
            <x-ui.choice type="radio" name="participation" value="hybrid" label="Mixta" />
            <x-ui.choice type="radio" name="participation" value="none" label="Sin participación" disabled />
        </fieldset>

        <div class="grid grid-cols-[minmax(0,1fr)] gap-2">
            <h3 class="text-lg font-bold tracking-tight">Conmutadores</h3>
            <x-ui.choice type="switch" name="allow_access" label="Permitir acceso a la aplicación" :checked="true"
                hint="La persona puede entrar con su usuario" />
            <x-ui.choice type="switch" name="public_profile" label="Perfil público" />
            <x-ui.choice type="switch" name="locked_switch" label="Conmutador deshabilitado" :checked="true"
                disabled />
        </div>
    </div>
</section>
