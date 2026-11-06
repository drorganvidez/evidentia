{{-- Minutes editor inside a form: two points with agreements, an empty one to try the validation and the live JSON of the hidden input --}}
@php
    $points = [
        [
            'id' => 41,
            'title' => 'Lectura y aprobación del acta anterior',
            'duration' => 10,
            'description' => 'Se aprueba el acta de la reunión 2 sin cambios.',
            'agreements' => [['id' => 7, 'description' => 'Publicar el acta de la reunión 2 en la web del comité.']],
        ],
        [
            'id' => 42,
            'title' => 'Presupuesto del photocall',
            'duration' => 25,
            'description' => 'Se revisan las tres ofertas recibidas y se compara el plazo de entrega de cada una.',
            'agreements' => [
                ['id' => 8, 'description' => 'Aceptar la oferta de Rotulación Sur por 480 euros.'],
                ['id' => null, 'description' => 'Pedir una segunda lona de repuesto.'],
            ],
        ],
        ['id' => null, 'title' => '', 'duration' => null, 'description' => '', 'agreements' => []],
    ];
@endphp
<section id="ui-minutes-editor" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-minutes-editor-title">
    <h2 id="ui-minutes-editor-title" class="text-2xl font-bold tracking-tight">Editor de actas</h2>

    <form method="get" action="{{ route('ui.index') }}#ui-minutes-editor" class="grid grid-cols-[minmax(0,1fr)] gap-4"
        data-demo="minutes">
        <x-ui.minutes-editor :points="$points" hint="El título de cada punto es obligatorio.">
            <div class="grid grid-cols-[minmax(0,1fr)] gap-1 border-t border-rule pt-3">
                <span class="text-xs font-semibold text-ink-muted">Valor del campo oculto</span>
                <output class="font-mono text-xs break-all text-ink-muted" x-text="json"></output>
            </div>
        </x-ui.minutes-editor>
        <div class="flex flex-wrap gap-3">
            <x-ui.button type="submit" variant="primary" icon="check">Guardar el acta</x-ui.button>
        </div>
    </form>
</section>
