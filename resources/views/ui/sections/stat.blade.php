{{-- Dashboard figures: the main card with a ring, legend and phone line, three cards hidden on the phone and the total hours without a link --}}
@php
    $link = route('ui.index') . '#ui-stat';
    $byStatus = [
        ['value' => 48, 'tone' => 'accepted', 'label' => 'Aceptadas'],
        ['value' => 36, 'tone' => 'pending', 'label' => 'Pendientes'],
        ['value' => 16, 'tone' => 'rejected', 'label' => 'Rechazadas'],
    ];
    $byType = [
        ['value' => 38, 'tone' => 'accepted', 'label' => 'Evidencias', 'text' => '4,00 h'],
        ['value' => 24, 'tone' => 'notice', 'label' => 'Reuniones', 'text' => '2,50 h'],
        ['value' => 22, 'tone' => 'pending', 'label' => 'Asistencias', 'text' => '2,25 h'],
        ['value' => 16, 'tone' => 'draft', 'label' => 'Bonificaciones', 'text' => '1,75 h'],
    ];
@endphp
<section id="ui-stat" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-stat-title">
    <h2 id="ui-stat-title" class="text-2xl font-bold tracking-tight">Estadísticas</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Horas aceptadas" value="4,00" unit="h" foot="de 8,25 h declaradas" tone="accepted"
            :href="$link" main class="md:col-span-2">
            <x-slot:aside>
                <x-ui.meter shape="ring" :segments="$byStatus" value="48 %"
                    label="48 % de las horas declaradas están aceptadas" />
            </x-slot:aside>
            <x-slot:legend>
                <span class="inline-flex items-center gap-1.5"><x-ui.dot tone="accepted" size="sm" />Aceptadas 4,00
                    h</span>
                <span class="inline-flex items-center gap-1.5"><x-ui.dot tone="pending" size="sm" />Pendientes 3,00
                    h</span>
                <span class="inline-flex items-center gap-1.5"><x-ui.dot tone="rejected" size="sm" />Rechazadas
                    1,25 h</span>
            </x-slot:legend>
            <x-slot:mini>
                <span>Pendientes 3,00 h</span>
                <span>Rechazadas 1,25 h</span>
            </x-slot:mini>
        </x-ui.stat>
        <x-ui.stat label="Pendientes de revisión" value="3" foot="evidencias por revisar" tone="pending"
            :href="$link" mobile="hide" />
        <x-ui.stat label="Reuniones" value="2" foot="asistidas este curso" tone="notice" :href="$link"
            mobile="hide" />
        <x-ui.stat label="Plazo de subida" value="9" unit="días" foot="hasta el 15 de noviembre"
            :href="$link" mobile="hide" />
        <x-ui.stat label="Horas computadas" value="10,50" unit="h" foot="de 20 h necesarias"
            class="md:col-span-2 lg:col-span-3">
            <x-ui.meter :segments="$byType" legend />
        </x-ui.stat>
    </div>
</section>
