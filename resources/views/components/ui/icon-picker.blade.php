{{--
    Icon picker: one radio per Lucide name under a legend, a preview of the chosen icon and a search over the names and
    their Spanish aliases. The value posted is a name, never HTML.
    <x-ui.icon-picker name="icon" label="Icono" :value="$committee->icon" required />
    <x-ui.icon-picker name="icon" :icons="['users', 'calendar', 'euro']" :columns="4" />
    icons null takes the curated list below; the search box is named <name>_search and is never posted as a value.
--}}
@props([
    'name' => 'icon',
    'label' => 'Icono',
    'value' => null,
    'icons' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'columns' => 6,
    'id' => null,
])
@php
    $catalogue = [
        'user-round' => 'persona, usuario',
        'users' => 'personas, grupo, equipo',
        'file-signature' => 'firma, acta, documento',
        'list-ordered' => 'lista, orden del día, puntos',
        'hand-helping' => 'ayuda, voluntariado, mano',
        'piggy-bank' => 'ahorro, hucha, dinero',
        'euro' => 'euro, dinero, finanzas',
        'warehouse' => 'almacén, nave, logística',
        'terminal' => 'terminal, consola, código',
        'megaphone' => 'megáfono, anuncio, difusión',
        'calendar' => 'calendario, fecha, agenda',
        'ticket' => 'entrada, ticket, acreditación',
        'camera' => 'cámara, foto, fotografía',
        'palette' => 'paleta, diseño, color',
        'globe' => 'globo, mundo, web',
        'mic' => 'micrófono, ponencia, charla',
        'rocket' => 'cohete, lanzamiento, inicio',
        'code' => 'código, programación, desarrollo',
        'cpu' => 'procesador, hardware, chip',
        'database' => 'base de datos, datos, almacenamiento',
        'radio' => 'radio, emisora, sonido',
        'video' => 'vídeo, grabación, cámara',
        'music' => 'música, nota, sonido',
        'coffee' => 'café, descanso, pausa',
        'utensils' => 'cubiertos, comida, catering',
        'bus' => 'autobús, transporte, viaje',
        'car' => 'coche, transporte, vehículo',
        'trophy' => 'trofeo, premio, concurso',
        'award' => 'premio, galardón, reconocimiento',
        'medal' => 'medalla, premio, mérito',
        'gift' => 'regalo, obsequio, sorpresa',
        'package' => 'paquete, envío, caja',
        'truck' => 'camión, transporte, reparto',
        'hammer' => 'martillo, montaje, obra',
        'wrench' => 'llave inglesa, herramienta, mantenimiento',
        'archive' => 'archivo, caja, histórico',
        'flask-conical' => 'matraz, laboratorio, ciencia',
        'leaf' => 'hoja, naturaleza, sostenibilidad',
        'recycle' => 'reciclaje, residuos, medio ambiente',
        'heart-pulse' => 'salud, latido, corazón',
        'brain' => 'cerebro, mente, ideas',
        'compass' => 'brújula, orientación, rumbo',
        'map' => 'mapa, plano, ubicación',
        'tent' => 'tienda de campaña, campamento, acampada',
        'drama' => 'teatro, máscaras, drama',
        'guitar' => 'guitarra, música, concierto',
        'paintbrush' => 'pincel, pintura, arte',
        'pen-tool' => 'pluma, diseño, vector',
        'scissors' => 'tijeras, recorte, manualidades',
        'ruler' => 'regla, medida, dibujo',
        'store' => 'tienda, comercio, puesto',
        'shopping-cart' => 'carrito, compra, pedido',
        'banknote' => 'billete, dinero, pago',
        'wallet' => 'cartera, dinero, fondos',
        'credit-card' => 'tarjeta, pago, cobro',
        'chart-pie' => 'gráfico de tarta, estadística, reparto',
        'chart-line' => 'gráfico de líneas, evolución, tendencia',
        'target' => 'diana, objetivo, meta',
        'crown' => 'corona, rey, premio',
        'gem' => 'gema, joya, patrocinio',
        'flame' => 'llama, fuego, energía',
        'zap' => 'rayo, energía, rápido',
        'cloud' => 'nube, tiempo, servidor',
        'umbrella' => 'paraguas, lluvia, protección',
        'droplets' => 'gotas, agua, lluvia',
        'mountain' => 'montaña, senderismo, excursión',
        'tree-pine' => 'pino, árbol, bosque',
        'bike' => 'bicicleta, ciclismo, transporte',
        'plane' => 'avión, viaje, vuelo',
        'ship' => 'barco, navegación, crucero',
        'anchor' => 'ancla, puerto, marina',
        'headphones' => 'auriculares, audio, sonido',
        'gamepad-2' => 'mando, videojuegos, juego',
        'smartphone' => 'móvil, teléfono, aplicación',
        'laptop' => 'portátil, ordenador, informática',
        'printer' => 'impresora, impresión, papel',
        'projector' => 'proyector, proyección, presentación',
        'presentation' => 'presentación, pizarra, charla',
        'newspaper' => 'periódico, prensa, noticias',
        'library' => 'biblioteca, libros, lectura',
        'party-popper' => 'fiesta, celebración, confeti',
        'cake' => 'tarta, cumpleaños, celebración',
        'speech' => 'discurso, conversación, charla',
        'message-circle' => 'mensaje, chat, conversación',
        'languages' => 'idiomas, traducción, lenguas',
        'accessibility' => 'accesibilidad, inclusión, apoyo',
        'graduation-cap' => 'graduación, birrete, estudios',
        'book-open' => 'libro, lectura, apuntes',
        'briefcase' => 'maletín, trabajo, empresa',
        'landmark' => 'edificio, institución, universidad',
        'scale' => 'balanza, justicia, equilibrio',
        'shield-check' => 'escudo, seguridad, verificado',
        'key-round' => 'llave, acceso, clave',
        'lightbulb' => 'bombilla, idea, innovación',
        'sparkles' => 'destellos, novedad, magia',
        'heart-handshake' => 'acuerdo, cariño, colaboración',
        'handshake' => 'apretón de manos, acuerdo, patrocinio',
        'puzzle' => 'puzle, pieza, integración',
        'star' => 'estrella, favorito, destacado',
    ];
    $names = $icons === null ? array_keys($catalogue) : array_values(array_unique(array_map('strval', $icons)));
    $aliases = [];
    foreach ($names as $icon) {
        $aliases[$icon] = $catalogue[$icon] ?? '';
    }

    $id ??= ui_field_id($name);
    $errorKey = ui_error_key($name);
    // Never assume $errors: the components also render through Blade::render
    $bag = $errors ?? null;
    if ($error === null && $bag instanceof \Illuminate\Support\ViewErrorBag) {
        $error = $bag->first($errorKey) ?: null;
    }
    $value = old($errorKey) ?? $value;
    $value = is_string($value) && in_array($value, $names, true) ? $value : null;

    $columnsClass = match ((int) $columns) {
        4 => 'grid-cols-4',
        5 => 'grid-cols-5',
        8 => 'grid-cols-5 md:grid-cols-8',
        default => 'grid-cols-5 md:grid-cols-6',
    };
    $describedBy = implode(
        ' ',
        array_filter([$error !== null ? $id . '-error' : null, $hint !== null ? $id . '-hint' : null]),
    );
@endphp
<fieldset x-data="iconPicker" data-aliases="{{ json_encode($aliases, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) }}"
    id="{{ $id }}" tabindex="-1" {{ $attributes->class(['grid min-w-0 grid-cols-[minmax(0,1fr)] gap-3']) }}
    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
    <legend class="text-sm font-semibold">{{ $label }}
        @if ($required)
            <span aria-hidden="true" class="text-rejected-ink after:content-['*']"></span>
            <span class="sr-only">(obligatorio)</span>
        @endif
    </legend>
    <div class="flex items-center gap-3">
        <span aria-hidden="true" x-ref="preview"
            class="grid size-10 shrink-0 place-items-center rounded-md bg-brand-soft text-brand-soft-ink">
            @if ($value !== null)
                <x-ui.icon :name="$value" class="size-5" />
            @endif
        </span>
        <span class="font-mono text-sm" x-text="value || 'Sin icono'">{{ $value ?? 'Sin icono' }}</span>
    </div>
    <div class="max-h-72 overflow-y-auto rounded-lg">
        <div class="sticky top-0 z-10 bg-glass pb-2">
            <x-ui.field :name="$name . '_search'" label="Buscar icono" type="search" icon="search" hide-label
                placeholder="Buscar icono" autocomplete="off" x-model="query"
                x-on:input.debounce.400ms="announceCount" />
        </div>
        <div class="{{ Arr::toCssClasses(['grid gap-1 p-1', $columnsClass]) }}">
            @foreach ($names as $icon)
                <label x-show="matches('{{ $icon }}')" title="{{ $icon }}"
                    class="relative grid size-11 cursor-pointer place-items-center rounded-sm text-ink-muted transition hover:bg-glass-2 has-checked:bg-brand-soft has-checked:text-brand-soft-ink has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-brand-hi">
                    <input type="radio" name="{{ $name }}" value="{{ $icon }}"
                        class="absolute inset-0 size-full cursor-pointer appearance-none opacity-0"
                        @checked($value === $icon) @required($required) x-on:change="choose('{{ $icon }}')">
                    <x-ui.icon :name="$icon" class="size-5" />
                    <span class="sr-only">{{ $icon }}, {{ $catalogue[$icon] ?? 'icono' }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <p role="status" class="sr-only" x-text="countText"></p>
    @if ($hint !== null)
        <p id="{{ $id }}-hint" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif
    @if ($error !== null)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-rejected-ink">
            <x-lucide-circle-alert class="size-4" aria-hidden="true" />{{ $error }}
        </p>
    @endif
</fieldset>
