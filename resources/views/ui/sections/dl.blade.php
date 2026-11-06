{{-- Definition lists: the details of an evidence, the call of a signature and the dense variant --}}
@php
    $now = now();
    $link = route('ui.index') . '#ui-dl';
@endphp
<section id="ui-dl" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-dl-title">
    <h2 id="ui-dl-title" class="text-2xl font-bold tracking-tight">Listas de definiciones</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-2">
        <x-ui.card title="Detalles de la evidencia" :level="3">
            <x-ui.dl>
                <x-ui.dl.item term="Registro" mono>EV-0033</x-ui.dl.item>
                <x-ui.dl.item term="Comité"><x-ui.committee-chip name="Logística" :id="7" /></x-ui.dl.item>
                <x-ui.dl.item term="Horas"><x-ui.hours :value="1.75" /></x-ui.dl.item>
                <x-ui.dl.item term="Estado">
                    <x-ui.badge tone="pending" pulse size="sm">Pendiente de revisión</x-ui.badge>
                </x-ui.dl.item>
                <x-ui.dl.item term="Integridad">
                    <span class="inline-flex items-center gap-1.5 font-medium text-accepted-ink">
                        <x-lucide-shield-check aria-hidden="true" class="size-4" />
                        Verificada
                    </span>
                </x-ui.dl.item>
                <x-ui.dl.item term="Subida"><x-ui.time :value="$now->copy()->subHours(6)" inline short /></x-ui.dl.item>
            </x-ui.dl>
        </x-ui.card>

        <x-ui.card title="Convocatoria de la firma" :level="3">
            <x-ui.dl>
                <x-ui.dl.item term="Fecha">
                    <x-ui.time :value="$now->copy()->addDays(3)->setTime(10, 0)" format="absolute" pattern="l j \d\e F, H:i" :mono="false" />
                </x-ui.dl.item>
                <x-ui.dl.item term="Lugar">Aula A2.12, ETSII</x-ui.dl.item>
                <x-ui.dl.item term="Modalidad">Presencial</x-ui.dl.item>
                <x-ui.dl.item term="Secretaría"><x-ui.person name="Marta" surname="Ruiz"
                        size="sm" /></x-ui.dl.item>
                <x-ui.dl.item term="Enlace" mono>
                    <a href="{{ $link }}"
                        class="break-all text-brand-ink pointer-coarse:inline-flex pointer-coarse:min-h-11 pointer-coarse:items-center">evidentia.us.es/sign/4821</a>
                </x-ui.dl.item>
            </x-ui.dl>
        </x-ui.card>

        <x-ui.card title="Variante densa" :level="3" padding="sm" class="lg:col-span-2">
            <x-ui.dl dense term-width="sm">
                <x-ui.dl.item term="UVUS" mono>lucperlop</x-ui.dl.item>
                <x-ui.dl.item term="Correo" mono>lucperlop@alum.us.es</x-ui.dl.item>
                <x-ui.dl.item term="Rol">Estudiante</x-ui.dl.item>
                <x-ui.dl.item term="Alta"><x-ui.time :value="$now->copy()->subMonths(2)" format="absolute"
                        pattern="d/m/Y" /></x-ui.dl.item>
            </x-ui.dl>
        </x-ui.card>
    </div>
</section>
