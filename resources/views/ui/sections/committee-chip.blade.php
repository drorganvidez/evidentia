{{-- Committee chips: the nine committees of the seeder with their hue by id, Lucide icons, the old Font Awesome value, links and the md size --}}
@use('App\Models\Committee')
@php
    $seeded = [
        [1, 'Presidencia', '<i class="fas fa-user-tie"></i>'],
        [2, 'Secretaría', '<i class="fas fa-file-signature"></i>'],
        [3, 'Programa', '<i class="fas fa-list-ol"></i>'],
        [4, 'Igualdad', '<i class="fas fa-people-carry"></i>'],
        [5, 'Sostenibilidad', '<i class="fas fa-piggy-bank"></i>'],
        [6, 'Finanzas', '<i class="fas fa-euro-sign"></i>'],
        [7, 'Logística', '<i class="fas fa-warehouse"></i>'],
        [8, 'Comunicación', '<i class="fab fa-twitter"></i>'],
        [9, 'I+D+I', '<i class="fas fa-terminal"></i>'],
    ];
    $committees = [];
    foreach ($seeded as [$id, $name, $icon]) {
        $committee = new Committee(['name' => $name, 'icon' => $icon]);
        $committee->id = $id;
        $committees[] = $committee;
    }
    $icons = ['crown', 'file-pen', 'list-ordered', 'scale', 'leaf', 'wallet', 'truck', 'megaphone', 'cpu'];
    $link = route('ui.index') . '#ui-committee-chip';
@endphp
<section id="ui-committee-chip" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-committee-chip-title">
    <h2 id="ui-committee-chip-title" class="text-2xl font-bold tracking-tight">Comités</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Desde el modelo, con el tono decidido por el identificador</h3>
        <ul class="flex flex-wrap items-center gap-3">
            @foreach ($committees as $committee)
                <li><x-ui.committee-chip :committee="$committee" /></li>
            @endforeach
        </ul>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Con un icono Lucide guardado</h3>
        <ul class="flex flex-wrap items-center gap-3">
            @foreach ($committees as $index => $committee)
                <li><x-ui.committee-chip :name="$committee->name" :id="$committee->id" :icon="$icons[$index]" /></li>
            @endforeach
        </ul>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Valor antiguo de Font Awesome, nombre que no existe, sin icono y
            sin identificador</h3>
        <ul class="flex flex-wrap items-center gap-3">
            <li><x-ui.committee-chip name="Presidencia" :id="1" :icon="$seeded[0][2]" /></li>
            <li><x-ui.committee-chip name="Programa" :id="3" icon="no-existe" /></li>
            <li><x-ui.committee-chip name="Logística" :id="7" /></li>
            <li><x-ui.committee-chip name="Sin identificador" /></li>
            <li><x-ui.committee-chip name="Tono explícito" :hue="300" /></li>
        </ul>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Enlace y tamaño md</h3>
        <ul class="flex flex-wrap items-center gap-3">
            <li><x-ui.committee-chip name="Logística" :id="7" icon="truck" :href="$link" /></li>
            <li><x-ui.committee-chip name="Comunicación" :id="8" :href="$link" /></li>
            <li><x-ui.committee-chip name="Logística" :id="7" icon="truck" size="md" /></li>
            <li><x-ui.committee-chip name="Comunicación" :id="8" size="md" :href="$link" /></li>
        </ul>
    </div>
</section>
