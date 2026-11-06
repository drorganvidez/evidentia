{{-- Avatars and people: initials, picture, sizes and the person line with its role --}}
<section id="ui-avatar" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-avatar-title">
    <h2 id="ui-avatar-title" class="text-2xl font-bold tracking-tight">Personas</h2>

    <div class="grid gap-3">
        <h3 class="text-lg font-bold tracking-tight">Avatar</h3>
        <ul class="flex flex-wrap items-center gap-6 text-sm">
            <li class="inline-flex items-center gap-2"><x-ui.avatar name="Juan" surname="Pérez" size="sm" />sm
            </li>
            <li class="inline-flex items-center gap-2"><x-ui.avatar name="Juan" surname="Pérez" />md</li>
            <li class="inline-flex items-center gap-2"><x-ui.avatar name="Juan" surname="Pérez" size="lg" />lg
            </li>
            <li class="inline-flex items-center gap-2"><x-ui.avatar name="Evidentia" :src="asset('img/brand/mark-256.png')" />Con imagen</li>
            <li class="inline-flex items-center gap-2"><x-ui.avatar name="Álvaro" surname="López" />Decorativo</li>
            <li class="inline-flex items-center gap-2"><x-ui.avatar name="María del Mar" surname="García"
                    :decorative="false" size="lg" />Con nombre accesible</li>
        </ul>
    </div>

    <div class="grid gap-3">
        <h3 class="text-lg font-bold tracking-tight">Persona</h3>
        <ul class="flex flex-wrap items-center gap-x-8 gap-y-4">
            <li><x-ui.person name="Juan" surname="Pérez" role="como coordinador de Programa" /></li>
            <li><x-ui.person name="Lucía" surname="Pérez" /></li>
            <li><x-ui.person name="Carlos" surname="Navarro" role="como coordinador de Programa" href="#ui-avatar" />
            </li>
            <li><x-ui.person name="Elena" surname="Martín" role="como presidenta" reverse /></li>
            <li><x-ui.person name="Juan" surname="Pérez" role="como estudiante" size="sm" /></li>
            <li><x-ui.person name="Lucía" surname="Pérez" size="sm" href="#ui-avatar" /></li>
        </ul>
    </div>
</section>
