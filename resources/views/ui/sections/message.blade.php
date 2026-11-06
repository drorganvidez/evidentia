{{-- A thread of messages to the people in charge: three messages, one of them mine, the open status and the reply form --}}
@php
    $now = now();
    $link = route('ui.index') . '#ui-message';
    $student = ['name' => 'Lucía', 'surname' => 'Pérez', 'role' => 'como estudiante', 'href' => $link];
    $coordinator = ['name' => 'Carlos', 'surname' => 'Navarro', 'role' => 'como coordinador de Programa'];
@endphp
<section id="ui-message" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-message-title">
    <h2 id="ui-message-title" class="text-2xl font-bold tracking-tight">Mensajes</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
            <x-ui.message :author="$student" :at="$now->copy()->subDays(2)" status="open">
                Hola, Carlos. He subido las pruebas del montaje del photocall, pero la evidencia sigue rechazada.
                ¿Falta algún documento?
            </x-ui.message>
            <x-ui.message :author="$coordinator" :at="$now->copy()->subDay()" mine>
                Hola, Lucía. Faltan las fotos del montaje terminado y la hoja de firmas del equipo.
            </x-ui.message>
            <x-ui.message :author="$student" :at="$now->copy()->subHours(3)">
                Acabo de adjuntar las fotos y la hoja de firmas. Gracias.
            </x-ui.message>
        </div>

        <x-ui.card title="Responder" :level="3">
            <form method="post" action="{{ route('ui.demo.submit') }}" class="grid grid-cols-[minmax(0,1fr)] gap-3">
                @csrf
                <label for="reply-body" class="text-sm font-semibold">Tu respuesta</label>
                <textarea id="reply-body" name="body" rows="4" placeholder="Escribe tu respuesta"
                    class="w-full min-w-0 rounded-sm border border-field-edge bg-field px-3 py-2 text-control text-ink placeholder:text-ink-muted"></textarea>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <x-ui.badge tone="pending" size="sm">Abierto</x-ui.badge>
                    <x-ui.button type="submit" variant="primary" icon="send">Enviar</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</section>
