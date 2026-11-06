{{-- Icon pickers: empty, with a value and with an error --}}
<section id="ui-icon-picker" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-icon-picker-title">
    <h2 id="ui-icon-picker-title" class="text-2xl font-bold tracking-tight">Selector de iconos</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 md:grid-cols-2 lg:grid-cols-3">
        <x-ui.icon-picker name="committee_icon" label="Icono del comité" required
            hint="Escribe para filtrar por nombre o por alias" />
        <x-ui.icon-picker name="event_icon" label="Icono del evento" value="megaphone" />
        <x-ui.icon-picker name="badge_icon" label="Icono de la insignia" :columns="5" error="Elige un icono" />
    </div>
</section>
