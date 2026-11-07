{{-- A diploma for a speaker, a contest winner or anybody else, linked to an account or to a free name --}}
@php
    $people = $users
        ->map(fn($user) => ['value' => (string) $user->id, 'label' => trim($user->surname . ', ' . $user->name, ', ')])
        ->values()
        ->all();
@endphp
<x-layout.app title="Emitir diploma" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Certificados y diplomas', 'href' => route('certificate.index')],
    ['label' => 'Emitir diploma'],
]">
    <x-ui.page-header title="Emitir diploma"
        lede="Elige una persona de la plataforma para que lo descargue desde su cuenta, o escribe el nombre de alguien de fuera." />

    <x-ui.card title="Datos del diploma" :level="2" class="max-w-2xl">
        <x-ui.form :action="route('certificate.store')" id="certificate-form" :fields="[
            'user_id' => 'Persona',
            'recipient_name' => 'Nombre',
            'title' => 'Motivo',
            'body' => 'Texto',
            'hours' => 'Horas',
            'issued_at' => 'Fecha',
        ]">
            <x-ui.field name="user_id" label="Persona" type="select" :options="$people" placeholder="Nadie de la plataforma" />
            <x-ui.field name="recipient_name" label="Nombre" hint="Obligatorio si no eliges a nadie; si eliges a alguien, sustituye a su nombre" maxlength="255" />
            <x-ui.field name="title" label="Motivo" required maxlength="255"
                hint="Completa la frase «por...», por ejemplo «impartir la charla Docker en producción»" />
            <x-ui.field name="body" label="Texto" type="textarea" :rows="3" :maxlength="2000" hint="Opcional, aparece bajo el motivo" />
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field name="hours" label="Horas" type="number" suffix="h" inputmode="decimal" min="0" step="0.25" />
                <x-ui.field name="issued_at" label="Fecha" type="date" :value="now()->format('Y-m-d')" required />
            </div>
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="award">Emitir diploma</x-ui.button>
                <x-ui.button variant="ghost" :href="route('certificate.index')">Cancelar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.app>
