{{-- End of a public signature: the result the controller left in the session --}}
@php
    $message = (string) session('sign_message');
    $signed = str_contains($message, 'correctamente');
@endphp
<x-layout.public title="Firma de asistencia">
    <x-ui.card title="Firma de asistencia" :level="1">
        <div class="grid gap-5">
            <x-ui.alert :tone="$signed ? 'accepted' : 'notice'" role="status" :title="$signed ? 'Firma registrada' : 'Firma no registrada'">
                {{ $message }}
            </x-ui.alert>
            <x-ui.button icon="arrow-left" :href="route('home')">Volver al inicio</x-ui.button>
        </div>
    </x-ui.card>
</x-layout.public>
