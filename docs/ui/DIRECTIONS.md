# Dos direcciones visuales para Evidentia

> Fase 1 del rediseño. Propuesta, sin código de producción. Las maquetas están en `direction-a.html` y `direction-b.html` (abrir con `index.html`); funcionan sin internet: fuentes y estilos van en `assets/`.
> Las dos comparten el encargo: interfaz de 2026, redondeada y amable, con brillo, que se vea como Evidentia y no como una plantilla de administración. Difieren en el carácter.

## Lo que tienen en común (decidido con tu feedback)

- **Aire.** 32 px entre bloques, 20–24 px entre fichas, 24–32 px de relleno dentro. Nada se toca.
- **Todo lo que informa, lleva a algún sitio.** Las fichas de resumen son enlaces (a la lista filtrada o al desglose de horas); cada fila del listado abre su detalle; el historial enlaza a quien actuó.
- **Si no es un botón, no parece un botón.** Los estados son etiquetas planas, pequeñas, junto a los metadatos. Los botones tienen relieve, altura de 42–44 px y color de marca; solo hay uno primario por pantalla.
- **Móvil con diseño propio** (≤ 720 px): barra de navegación inferior, un solo resumen compacto, filtros deslizables, filas convertidas en tarjetas con lo esencial (título, estado, horas, fecha), recorrido del detalle en vertical. Comprobado a 400 px sin desbordamientos.
- **Sin emojis.** Iconos SVG en línea (un único set, Lucide, servido desde la app).
- **Modo claro, oscuro y "sistema"**, solo con variables; el usuario elige arriba a la derecha.
- **Pie de página** en una sola fila: marca y versión (leída del fichero `.version` que ya genera el Dockerfile; al pulsarla se abren las novedades), repositorio, licencia, ayuda y buzón, y la Universidad a la derecha.
- **Vista "Registro de actividad"** para profesorado y presidencia (ver `TRAZABILIDAD.md`), y una vista nueva **"Mis horas"** con el desglose de lo que computa.
- **Accesibilidad**: contraste AA verificado por cálculo en todos los pares texto/fondo que se usan como texto (`ink` e `ink-muted` sobre canvas, cristal y cristal-2 en claro y oscuro; `brand-ink`; blanco sobre `brand` y `brand-lo`; cada `*-ink` sobre su `*-soft`). `ink-faint` y `brand-hi` no llegan a 4,5:1 y se reservan para usos no textuales (reglas, marcadores, anillos de foco, halos). Foco visible, *skip link*, `aria-current`, tablas con `caption`, `prefers-reduced-motion`, sin desbordes a 320 px.

## Dirección A — «Aurora» (recomendada)

**Concepto.** *La evidencia sale a la luz.* La aplicación es una superficie de cristal esmerilado sobre una luz viva. Las "auroras" del fondo (índigo, cian y rosa, muy desenfocadas y en movimiento lento) dan el brillo; el cristal (desenfoque de fondo, borde luminoso, sombra suave) ordena. Es la traducción actual del "deslumbre" de Aero: translucidez, profundidad y color, pero con una jerarquía tranquila encima.

**Tipografía.** *Plus Jakarta Sans* variable (OFL) para todo, con interletrado negativo en títulos (−0,035 em) y pesos 450/550/650/750. *JetBrains Mono* variable (OFL) para cifras, registros (`EV-0033`), fechas y horas: alinea columnas y se reconoce como dato. Dos ficheros woff2, 68 KB en total, subconjunto latino, `font-display: swap`, precarga de la principal.

**Color (OKLCH).** Marca `aurora` (tono 285, croma 0,20: índigo eléctrico). Neutros `slate` fríos con un punto de azul (croma 0,022, constante en toda la escala, para que el fondo claro sea "papel azulado" y el oscuro "tinta", no gris). Acentos `cyan` y `rose` solo para las auroras y degradados. Semánticos: `exito` 155, `aviso` 75, `error` 25, `info` 240. Las escalas 50–950 se generan con una curva de luminosidad fija y el croma ajustado al gamut sRGB; las tablas están al final.
- Claro: fondo `slate-50`, texto `slate-900`, secundario `slate-600`, botón `aurora-600` con texto blanco (8,3:1), enlaces `aurora-700`.
- Oscuro: fondo `slate-950`, texto `slate-100`, secundario `slate-400`, enlaces `aurora-300`, chips `*-900`/`*-300`.

**Forma y espacio.** Radios 12 (controles) / 16 / 20 (fichas) / 28 (paneles) y píldora para botones y chips. Bordes de 1 px translúcidos; profundidad por sombra de color (no gris) y por el borde superior luminoso del cristal. Filas de tabla de 68 px, densas pero legibles; la densidad sube en las vistas de gestión (56 px) sin cambiar el sistema.

**Firma visual.**
1. **El orbe de estado**: una píldora con un punto que emite luz. Borrador gris, pendiente ámbar y *late* suavemente, aceptada verde, rechazada roja. Es el mismo elemento en listados, detalle e historial.
2. **Cristal suave sobre aurora**: las superficies son casi sólidas (92 % en claro, 88 % en oscuro) con un desenfoque leve, así que el color del fondo se intuye sin que nada sea translúcido. La maqueta lleva un interruptor "Cristal suave / Intenso": el modo intenso es la primera versión, que descartaste por demasiado *aero glass*.
3. **El recorrido**: la línea de estados del detalle (borrador → enviada → revisada → aceptada) con nodos luminosos, en horizontal en escritorio y vertical en móvil.

**Movimiento.** Auroras a la deriva (26–38 s, alternas); botones que se elevan 1 px con más halo; filas que se iluminan al pasar; el orbe "pendiente" late cada 2,2 s. Todo con `cubic-bezier(.2,.8,.2,1)` y 180 ms; con `prefers-reduced-motion` se para todo.

**Marca.** Cuadrado redondeado con degradado aurora→cian y una "e" blanca; funciona como favicon (SVG), icono de app y en el pie. Sustituye al logo actual y a los PNG sin enlazar.

## Dirección B — «Bento»

**Concepto.** *El mural del congreso.* Un tablón de fichas sólidas, cálidas y ordenadas, como las tarjetas de un panel de organización. Sin cristal: superficies blancas sobre papel arena, sombras suaves y mucho radio. Es la opción más "amable": cercana, de equipo, menos tecnológica.

**Tipografía.** *Outfit* variable (OFL) para títulos, cifras y botones: geométrica y redonda. *Figtree* variable (OFL) para el texto. *JetBrains Mono* para datos. Tres ficheros, 93 KB.

**Color.** Marca `coral` (tono 38, croma 0,18) con acento `menta` (170) para plazos y pasos completados. Neutros `arena` cálidos. Botón `coral-600` con texto blanco (pasa AA); el `coral-500`, más vivo, se reserva a halos, degradados y la portada del detalle.

**Forma y espacio.** Radios 16 / 22 / 28 y píldora. Fichas con borde 1 px `arena-200` y sombra en dos capas. El listado no es una tabla a rayas: cada fila es una **ficha con lomo** de color de estado a la izquierda, separada 12 px de la siguiente.

**Firma visual.**
1. **El lomo de estado**: la franja de color de 6 px al borde de cada ficha, que también aparece en móvil.
2. **Burbujas de icono**: círculos y cuadrados redondeados tintados que acompañan a cada métrica, paso del recorrido y entrada del historial.
3. **La portada**: el detalle de una evidencia abre con una portada en degradado de marca, como la cubierta de un expediente de cartón.

**Movimiento.** Sin fondo animado; elevación de 2 px en fichas y botones, 180 ms. Igual de sobrio y con la misma regla de `prefers-reduced-motion`.

**Marca.** Cuadrado redondeado coral con una "e" blanca y un punto menta en la esquina: amable y reconocible a 16 px.

## Recomendación: A «Aurora»

- Es la que cumple lo que pediste: brillo y profundidad reales, sin dejar de ser una herramienta seria. B es más amable pero más común (se parece a muchos paneles de 2024).
- El cristal resuelve bien las vistas de gestión, que son muchas tablas: el color del fondo diferencia pantallas sin añadir elementos.
- Una sola familia tipográfica más la mono simplifica el sistema y pesa menos.
- El orbe de estado es un elemento propio y se lee igual en claro, oscuro, impreso en los PDF (sin halo) y en móvil.
- Riesgo que hay que controlar: el desenfoque de fondo cuesta en móviles antiguos; con el cristal suave el desenfoque es pequeño y la implementación degradará a fondo sólido donde `backdrop-filter` no esté disponible; las auroras se paran con `prefers-reduced-motion`.

Si eliges B, nada de lo técnico cambia: los tokens, los componentes y la estructura son los mismos; cambian valores y tres detalles de firma.

## Lo que necesito que decidas

1. **Dirección**: confirmar A con cristal suave (es la que te gustó: "con esa paleta, es precioso"), o B.
2. **Trazabilidad**: revisar la propuesta de `TRAZABILIDAD.md` (qué se anota, quién lo ve, cuánto se guarda).
3. **Pie de página**: ¿enlaces al repositorio y a la licencia como ahora, o solo versión y buzón para los alumnos?

#### Escalas de la dirección A (OKLCH; hex orientativo)

| Escala | 50 | 100 | 200 | 300 | 400 | 500 | 600 | 700 | 800 | 900 | 950 |
|---|---|---|---|---|---|---|---|---|---|---|---|
| `aurora` | #f6f6ff | #eaebff | #d4d4ff | #b8b6ff | #9a93ff | #7d6af5 | #624ad3 | #5038b3 | #3c298c | #2a1c64 | #180e40 |
| `slate` | #f3f7fe | #e9edf5 | #d4d9e3 | #babfcb | #9da3b0 | #7e8592 | #616875 | #4e5460 | #3b404b | #282d36 | #151922 |
| `cyan` | #e9faff | #d3f3fc | #a6e5f5 | #66d0e9 | #00b6d4 | #0095ad | #007488 | #005e6f | #004855 | #00333d | #001e24 |
| `rose` | #fff3f9 | #ffe4f2 | #fdc7e4 | #f3a2d0 | #e17bb8 | #c8539c | #a6327d | #8b2267 | #6c174f | #4d1038 | #300621 |
| `exito` | #eefaf2 | #def3e4 | #bbe5c8 | #8fd1a6 | #5fb982 | #249d5e | #007c45 | #006537 | #004d29 | #00371b | #00200e |
| `aviso` | #fff5e9 | #fbead3 | #f4d4a8 | #e7b670 | #d4952d | #b17700 | #8b5d00 | #724b00 | #573900 | #3e2700 | #261600 |
| `error` | #fff4f3 | #ffe6e4 | #ffcac5 | #ffa39b | #f8756e | #e04746 | #bc2028 | #9e0b1a | #7c0210 | #58050b | #380104 |
| `info` | #eff8ff | #dcf0ff | #b4e0ff | #83c8f8 | #4cadea | #008dd0 | #006fa4 | #005a86 | #004468 | #00304b | #001c2e |

#### Escalas de la dirección B (OKLCH; hex orientativo)

| Escala | 50 | 100 | 200 | 300 | 400 | 500 | 600 | 700 | 800 | 900 | 950 |
|---|---|---|---|---|---|---|---|---|---|---|---|
| `coral` | #fff4f1 | #ffe7df | #ffccbc | #ffa589 | #f27d58 | #da5224 | #b33600 | #932b00 | #721f00 | #521400 | #330900 |
| `arena` | #fbf6f0 | #f2ece4 | #dfd8cf | #c7beb3 | #aba196 | #8e8377 | #70665a | #5c5248 | #473e35 | #332b23 | #1f1811 |
| `menta` | #edfaf5 | #dbf3ea | #b6e5d4 | #86d1b7 | #4eb998 | #009c79 | #007a5e | #00634c | #004c3a | #003628 | #002016 |
| `exito` | #effaf1 | #def3e3 | #bde5c6 | #93d1a3 | #65b87e | #309c59 | #007d3e | #006531 | #004e24 | #003718 | #00210b |
| `aviso` | #fef6e9 | #faebd3 | #f1d5a7 | #e3b86f | #cf9828 | #ac7a00 | #875f00 | #6e4d00 | #553a00 | #3c2800 | #241700 |
| `error` | #fff4f3 | #ffe6e5 | #ffcac7 | #ffa29f | #f77474 | #e0464e | #bc1e31 | #9e0823 | #7c0118 | #580410 | #380006 |
| `info` | #f1f8ff | #e0efff | #bdddff | #8fc4fc | #60a8ef | #2d88da | #006ab7 | #005696 | #004174 | #002e54 | #001a34 |
