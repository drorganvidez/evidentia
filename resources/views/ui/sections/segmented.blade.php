{{-- Segmented controls: meeting type, modality with icons, attendance status, block, with error, small, icons only and the theme switch --}}
@php
    $types = [['value' => 1, 'label' => 'Ordinaria'], ['value' => 2, 'label' => 'Extraordinaria']];
    $modalities = [
        ['value' => 'onsite', 'label' => 'Presencial', 'icon' => 'map-pin'],
        ['value' => 'online', 'label' => 'Virtual', 'icon' => 'video'],
        ['value' => 'hybrid', 'label' => 'Híbrida', 'icon' => 'monitor'],
    ];
    $statuses = [
        ['value' => 'present', 'label' => 'Asistió'],
        ['value' => 'absent', 'label' => 'Faltó'],
        ['value' => 'excused', 'label' => 'Justificada'],
    ];
@endphp
<section id="ui-segmented" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-segmented-title">
    <h2 id="ui-segmented-title" class="text-2xl font-bold tracking-tight">Control segmentado</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
        <div class="glass-2 grid content-start gap-2 rounded-lg p-4" data-demo="type">
            <span class="font-mono text-2xs text-ink-muted">options · value</span>
            <x-ui.segmented name="demo-type" legend="Tipo de reunión" :options="$types" :value="1" />
        </div>
        <div class="glass-2 grid content-start gap-2 rounded-lg p-4" data-demo="modality">
            <span class="font-mono text-2xs text-ink-muted">icon · legend visible</span>
            <x-ui.segmented name="demo-modality" legend="Modalidad" :hide-legend="false" :options="$modalities"
                value="online" />
        </div>
        <div class="glass-2 grid content-start gap-2 rounded-lg p-4" data-demo="block">
            <span class="font-mono text-2xs text-ink-muted">block · opciones a lo ancho</span>
            <x-ui.segmented name="demo-status" legend="Estado de asistencia" :options="$statuses" value="present" block />
        </div>
        <div class="glass-2 grid content-start gap-2 rounded-lg p-4" data-demo="error">
            <span class="font-mono text-2xs text-ink-muted">error · aria-describedby</span>
            <x-ui.segmented name="demo-type-error" legend="Tipo de reunión" :options="$types"
                error="Elige el tipo de reunión." />
        </div>
        <div class="glass-2 grid content-start gap-2 rounded-lg p-4" data-demo="small">
            <span class="font-mono text-2xs text-ink-muted">size="sm" · icon-only</span>
            <div class="flex flex-wrap items-center gap-4">
                <x-ui.segmented name="demo-type-sm" legend="Tipo de reunión" :options="$types" :value="2"
                    size="sm" />
                <x-ui.segmented name="demo-modality-icons" legend="Modalidad" :options="$modalities" value="hybrid"
                    icon-only />
                <x-ui.segmented name="demo-modality-icons-sm" legend="Modalidad" :options="$modalities" value="onsite"
                    icon-only size="sm" />
            </div>
        </div>
        <div class="glass-2 grid content-start gap-2 rounded-lg p-4" data-demo="theme">
            <span class="font-mono text-2xs text-ink-muted">theme-switch · icon-only y labels</span>
            <div class="flex flex-wrap items-center gap-4">
                <x-ui.theme-switch name="demo-theme" />
                <x-ui.theme-switch name="demo-theme-labels" labels />
            </div>
        </div>
    </div>
</section>
