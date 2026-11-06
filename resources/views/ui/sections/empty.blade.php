{{-- Empty states: lists without items, a compact one, an error page and a blocked account --}}
<section id="ui-empty" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-empty-title">
    <h2 id="ui-empty-title" class="text-2xl font-bold tracking-tight">Estados vacíos</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-2">
        <div class="glass-2 rounded-lg">
            <x-ui.empty title="No hay evidencias" text="Cuando subas una aparecerá aquí.">
                <x-slot:actions>
                    <a href="{{ route('ui.index') }}#ui-empty"
                        class="inline-flex h-10.5 items-center gap-2 rounded-full bg-brand px-5 text-md font-semibold text-on-brand no-underline shadow-glow transition hover:bg-brand-lo pointer-coarse:min-h-11">
                        <x-lucide-plus aria-hidden="true" class="size-4.5" />
                        Nueva evidencia
                    </a>
                </x-slot:actions>
            </x-ui.empty>
        </div>

        <div class="glass-2 rounded-lg">
            <x-ui.empty icon="calendar-x" title="No hay reuniones" text="Tu comité todavía no ha convocado ninguna." />
        </div>

        <div class="glass-2 rounded-lg">
            <x-ui.empty icon="file-x" title="Sin pruebas" size="sm" />
        </div>

        <div class="glass-2 rounded-lg">
            <x-ui.empty icon="lock" title="Tu cuenta está bloqueada" text="Un responsable tiene que desbloquearla.">
                <x-slot:actions>
                    <form method="post" action="{{ route('ui.index') }}">
                        @csrf
                        <button type="submit"
                            class="glass-2 inline-flex h-10.5 items-center gap-2 rounded-full px-5 text-md font-semibold text-ink transition hover:bg-glass-hi pointer-coarse:min-h-11">
                            <x-lucide-log-out aria-hidden="true" class="size-4.5" />
                            Cerrar sesión
                        </button>
                    </form>
                </x-slot:actions>
            </x-ui.empty>
        </div>

        <div class="glass-2 rounded-lg lg:col-span-2">
            <x-ui.empty code="404" title="No encontramos la página" text="La dirección no existe o ha cambiado.">
                <x-slot:actions>
                    <a href="{{ route('ui.index') }}"
                        class="inline-flex h-10.5 items-center gap-2 rounded-full bg-brand px-5 text-md font-semibold text-on-brand no-underline shadow-glow transition hover:bg-brand-lo pointer-coarse:min-h-11">
                        <x-lucide-arrow-left aria-hidden="true" class="size-4.5" />
                        Volver al inicio
                    </a>
                </x-slot:actions>
            </x-ui.empty>
        </div>
    </div>
</section>
