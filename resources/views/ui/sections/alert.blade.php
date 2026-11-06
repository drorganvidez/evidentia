{{-- Alerts: a rejection with its quote, authorship and action, a deadline, a signature result, an access error, dismissible and compact --}}
<section id="ui-alert" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-alert-title">
    <h2 id="ui-alert-title" class="text-2xl font-bold tracking-tight">Alertas</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4">
        <x-ui.alert tone="rejected" title="Tu coordinador necesita más pruebas"
            quote="Las fotos no muestran el photocall terminado. Sube una imagen del montaje final y el albarán del material.">
            <x-slot:meta>
                <x-ui.person name="Juan" surname="Pérez" size="sm" />
                <span aria-hidden="true">·</span>
                <x-ui.time :value="now()->subHours(6)" inline short />
            </x-slot:meta>
            <x-slot:actions>
                <x-ui.button variant="primary" icon="pen-line">Corregir y volver a enviar</x-ui.button>
                <x-ui.button variant="ghost" icon="message-square">Responder</x-ui.button>
            </x-slot:actions>
        </x-ui.alert>

        <x-ui.alert tone="pending" title="El plazo de subida termina el viernes">
            <p>Quedan tres días para subir las evidencias de esta edición.</p>
            <x-slot:actions>
                <x-ui.button icon="plus">Nueva evidencia</x-ui.button>
            </x-slot:actions>
        </x-ui.alert>

        <x-ui.alert tone="accepted" role="status" title="Firma registrada">
            <p>Tu asistencia a la reunión 3 ha quedado registrada a las 18:42.</p>
        </x-ui.alert>

        <x-ui.alert tone="rejected" role="alert" title="No hemos podido iniciar sesión">
            <p>El usuario o la contraseña no son correctos.</p>
        </x-ui.alert>

        <x-ui.alert dismissible>
            <p>La memoria de la edición anterior ya está publicada en la sección de documentos.</p>
        </x-ui.alert>

        <x-ui.alert tone="draft" icon="history" title="Borrador guardado" compact>
            <p>Puedes seguir editando la evidencia más tarde.</p>
        </x-ui.alert>

        <x-ui.alert tone="accepted" compact>
            <p>Convocatoria enviada a los 14 miembros del comité.</p>
        </x-ui.alert>
    </div>
</section>
