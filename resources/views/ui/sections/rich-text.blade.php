{{--
    Rich text: every tag the purifier keeps, run through Html::clean as the views will, plus the clamped and small forms.
    The inline style attributes the purifier allows are left out on purpose: the strict policy blocks them and the
    Playwright fixture fails on any policy message. The prose styles arrive with the import of prose.css (lote 6).
--}}
@php
    $sample = implode('', [
        '<h2>Montaje del photocall</h2>',
        '<p>El equipo de <strong>Logística</strong> montó el <em>photocall</em> del vestíbulo en dos tardes, ',
        'con el apoyo de <a href="https://evidentia.us.es" target="_blank">la organización</a>.</p>',
        '<ul><li>Estructura de aluminio de 3 × 2 m</li><li>Lona impresa con los patrocinadores</li><li>Dos focos LED</li></ul>',
        '<ol><li>Recogida del material</li><li>Montaje</li><li>Prueba de luz</li></ol>',
        '<blockquote>Quedó listo veinte minutos antes de abrir las puertas.</blockquote>',
        '<pre><code>npm run build</code></pre>',
        '<table><thead><tr><th>Material</th><th>Unidades</th></tr></thead>',
        '<tbody><tr><td>Focos</td><td>2</td></tr><tr><td>Lona</td><td>1</td></tr></tbody></table>',
        '<p><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAYAAAAGCAIAAABvrngfAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAFElEQVQImWNM8rrMgAqYGDAANYUAqj0Bi085lBwAAAAASUVORK5CYII=" ' .
        'alt="Lona del photocall con el logotipo" width="120" height="48"></p>',
        '<hr>',
        '<p>Código de referencia <code>EV-0033</code>.</p>',
    ]);
    $bio =
        '<p>Estudiante de cuarto curso. Coordina el comité de <b>Comunicación</b> desde 2025 y colabora con el de Programa ' .
        'en la selección de ponentes. Antes participó como voluntaria en dos ediciones y redactó la memoria de la de 2024.</p>' .
        '<p>Le interesan la accesibilidad y el diseño de servicios.</p>';
@endphp
<section id="ui-rich-text" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-rich-text-title">
    <h2 id="ui-rich-text-title" class="text-2xl font-bold tracking-tight">Texto enriquecido</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <x-ui.card title="Descripción purificada" :level="3">
            <x-ui.rich-text :html="Html::clean($sample)" />
        </x-ui.card>

        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-4">
            <x-ui.card title="Recortado a tres líneas" :level="3">
                <x-ui.rich-text :html="Html::clean($bio)" :clamp="3" />
            </x-ui.card>
            <x-ui.card title="Tamaño pequeño" :level="3">
                <x-ui.rich-text :html="Html::clean($bio)" size="sm" />
            </x-ui.card>
        </div>
    </div>
</section>
