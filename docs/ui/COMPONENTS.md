# Biblioteca de componentes

Referencia de la interfaz nueva de Evidentia, la de la v4.0.0 «Aurora» que usan todas las pantallas. Todo lo que aparece aquí se ve en `/_ui`, el catálogo que se sirve solo en los entornos `local` y `testing`, y en sus páginas compuestas (`/_ui/shell`, `/_ui/public`, `/_ui/detail`, `/_ui/pdf`). El catálogo se renderiza con el shell real de la aplicación, así que es también su primer consumidor.

## Cómo verlo

Con la demo levantada (ver `README.md`), abre `http://localhost:8000/_ui`. La tabla de contenidos enlaza las 49 secciones; la barra lateral agrupa el catálogo y las páginas. El tema claro, oscuro o sistema se cambia desde la topbar (escritorio) o el cajón (teléfono) y se recuerda.

## Convenciones

- Un componente es un fichero Blade en `resources/views/components/ui`, `layout` o `pdf`, con un comentario inicial que enseña su uso. Las props se declaran con `@props`; pasar `null` a una prop devuelve su valor por defecto (Blade usa `??`), de modo que para ocultar algo se pasa `false` o una cadena vacía, nunca `null`.
- Las clases son solo las utilidades generadas desde los tokens de `resources/css/app.css` (no existe la paleta de serie de Tailwind). Los colores de estado son `accepted`, `pending`, `rejected`, `draft` y `notice`, cada uno con `-soft`, `-glass` e `-ink`.
- `text-ink-muted` solo sobre cristal (`glass`, `glass-2`). Sobre el lienzo desnudo, donde se mueven las manchas de la aurora, el texto va en `text-ink`; `text-ink-faint` nunca lleva texto.
- Nada que no sea un botón parece un botón. Las tarjetas con `href` son un único enlace y no admiten controles dentro.
- Una sola `h1` por página, la del `page-header`, a la que apunta `main` con `aria-labelledby`. Las secciones y tarjetas bajan de nivel con la prop `level`.
- Ningún elemento toma el foco al cargar salvo el resumen de errores de `form`. Los diálogos nativos devuelven el foco a su disparador.
- Los textos de la interfaz van en español, sin copy explicativo y sin emojis. Las fechas y los plazos fuerzan el locale `es`.
- Los ids de los controles se derivan del `name` con `ui_field_id()` (`users[]` pasa a `field-users`) y la clave del error con `ui_error_key()`. Dos instancias del mismo componente en una página necesitan `id` o `name` distintos; `DesignSystemPageTest` comprueba que ningún id se repite en el catálogo ni en las páginas.
- Todo el comportamiento vive en módulos de `resources/js/components` descubiertos por `import.meta.glob` (el nombre del fichero es el `x-data`) y los stores en `resources/js/stores` (el nombre del fichero es el de `$store`). `app.js` registra todos; `public.js` solo `busyForm`, `charCount`, `clipboard`, `countUp`, `dismissible`, `notices`, `pageProgress`, `passwordField`, `passwordRules` y `selectField` con los stores `theme` y `notices`.
- Ninguna vista nueva lleva `style=`, manejadores `on*=` ni `<script>` sin nonce (`CspSafeViewsTest`). La CSP es estricta y Alpine corre en su build CSP (ver la gramática más abajo).

## Componentes

| Componente | Fichero | Alpine | Uso |
| --- | --- | --- | --- |
| `x-ui.aurora` | `ui/aurora.blade.php` | — | Fondo de luz de la dirección Aurora, tres manchas lentas tras la página; `intense` en las públicas. |
| `x-ui.brand` | `ui/brand.blade.php` | — | Marca y nombre enlazados al inicio, con subtítulo opcional o solo la marca (`mark-only`). |
| `x-ui.skip-link` | `ui/skip-link.blade.php` | — | Primer elemento del cuerpo, visible solo con el foco, salta al contenido o al destino dado. |
| `x-ui.icon` | `ui/icon.blade.php` | — | Icono Lucide con nombre dinámico; con nombre fijo se escribe `<x-lucide-nombre />`. |
| `x-ui.button` | `ui/button.blade.php` | `clipboard` (con `copy`) | Botón o enlace con aspecto de botón; variantes `primary`, `quiet`, `danger`, `ghost`; `icon-only` exige `label`. |
| `x-ui.dot` | `ui/dot.blade.php` | — | Punto de estado decorativo; el texto contiguo lleva el significado. |
| `x-ui.badge` | `ui/badge.blade.php` | — | Etiqueta de estado, chip (`variant="chip"`, con `hue` o `mono`) o contador (`variant="count"`). |
| `x-ui.avatar` | `ui/avatar.blade.php` | — | Iniciales sobre el degradado o la foto de una persona; decorativo salvo que se pida nombre. |
| `x-ui.person` | `ui/person.blade.php` | — | Avatar, nombre y línea de rol; el nombre es enlace con `href`. |
| `x-ui.time` | `ui/time.blade.php` | — | Fecha relativa en español con la absoluta para el registro y `datetime` legible por máquinas. |
| `x-ui.hours` | `ui/hours.blade.php` | — | Horas con coma decimal y cifras tabulares. |
| `x-ui.meter` | `ui/meter.blade.php` | — | Barra apilada o anillo de porcentajes por tono, sin estilos en línea (`w-N/100`). |
| `x-ui.card` | `ui/card.blade.php` | — | Tarjeta de cristal con título, `extra`, cuerpo y pie; con `href` es un único enlace; `halo` para la destacada. |
| `x-ui.empty` | `ui/empty.blade.php` | — | Estado vacío con icono o código (`404`), título real, una frase y acciones. |
| `x-ui.nav` | `ui/nav.blade.php` | — | Navegación principal como lista lateral (raíl entre `md` y `lg`), raíl fijo o barra inferior del teléfono. |
| `x-ui.breadcrumbs` | `ui/breadcrumbs.blade.php` | — | Migas de la topbar; la última es la página actual y las anteriores se ocultan en el teléfono. |
| `x-ui.page-header` | `ui/page-header.blade.php` | — | Fila del título de una pantalla, la única `h1`, con `eyebrow`, `lede`, `meta` y `actions`. |
| `x-ui.footer` | `ui/footer.blade.php` | — | Pie con marca, píldora de versión (`evidentia_version()`), enlaces e institución; `compact` en las públicas. |
| `x-ui.deadline` | `ui/deadline.blade.php` | `deadline` | Tarjeta de plazo de la barra lateral o su línea `inline` bajo el título; cuenta atrás en vivo. |
| `x-ui.dialog` | `ui/dialog.blade.php` | `dialog` | `<dialog>` nativo con su disparador; `modal`, `alert` (confirmación) o `drawer` (cajón). Bajo `md`, hoja inferior. |
| `x-ui.menu`, `x-ui.menu.item` | `ui/menu/` | `menu` | Botón de menú (patrón APG) con ítems enlace, botón, formulario (`method`) o separador. |
| `x-ui.segmented`, `x-ui.segmented.option` | `ui/segmented/` | — | Radios nativos en una píldora para una elección exclusiva. |
| `x-ui.theme-switch` | `ui/theme-switch.blade.php` | `$store.theme` | Tema claro, oscuro o sistema como tres radios ligados al store; cada instancia con su `name`. |
| `x-ui.notices` | `ui/notices.blade.php` | `notices` + `$store.notices` | Pila de avisos (flash de sesión y eventos `ui-notice`) con las dos regiones vivas de la página, enlace opcional y cola; una por página. |
| `x-ui.bell` | `ui/bell.blade.php` | — | Campana de la topbar, enlace a la bandeja con el número de no leídos en su nombre. |
| `x-ui.search` | `ui/search.blade.php` | `shortcut` + `$store.shortcuts` | Formulario GET de búsqueda con atajo de teclado (`/`) que se puede apagar; oculto en el teléfono salvo en el cajón. |
| `x-ui.count-chip` | `ui/count-chip.blade.php` | `countUp` | Cifra de la página de acceso que cuenta desde cero, con punto de tono y unidad. |
| `x-ui.scene` | `ui/scene.blade.php` | — | Escena decorativa de las páginas públicas con piezas de la plataforma (una evidencia aceptada, las horas por origen, una reunión con su acta, un bono y chips de comités) y datos inventados, oculta con `aria-hidden`; sigue su propio ancho y por debajo de 34 rem (el teléfono, la tableta horizontal) deja solo la evidencia. `variant="sides"` deja piezas sin texto en los márgenes desde `lg`. |
| `x-ui.table`, `x-ui.table.row`, `x-ui.table.cell` | `ui/table/` | `tableFilter`, `scrollRegion`, `rowLink` | Tabla de datos con orden en la URL, filas enlazadas y tarjetas en el teléfono; sin paginador, filtro rápido en el navegador; con `paginator`, búsqueda `q` en el servidor, páginas y filas por página en el pie. |
| `x-ui.tabs` | `ui/tabs.blade.php` | — | Filtros o pestañas por URL; el servidor filtra y el enlace actual lleva `aria-current`. |
| `x-ui.pagination` | `ui/pagination.blade.php` | — | Enlaces de un paginador de Laravel con ventana alrededor de la página actual, la primera y la última siempre; en el teléfono, primera, anterior, «Página 7 de 13», siguiente y última. |
| `x-ui.stat` | `ui/stat.blade.php` | — | Tarjeta de cifra del panel con etiqueta, valor, unidad y pie; enlace con `href`. |
| `x-ui.bar-list` | `ui/bar-list.blade.php` | — | Lista de barras ya ordenada por el servidor, relativa a `max`. |
| `x-ui.dl`, `x-ui.dl.item` | `ui/dl/` | — | Pares término y valor como lista de definiciones real. |
| `x-ui.timeline`, `x-ui.timeline.item` | `ui/timeline/` | — | Pasos de un proceso (`variant="steps"`) o historial de eventos (`variant="log"`). |
| `x-ui.evidence-status` | `ui/evidence-status.blade.php` | — | Estado de una evidencia como etiqueta (`DRAFT`, `PENDING`, `ACCEPTED`, `REJECTED`, `BIN`), con el motivo en `title`. |
| `x-ui.attendee-status` | `ui/attendee-status.blade.php` | — | Asistencia de Eventbrite como etiqueta desde el literal almacenado. |
| `x-ui.event-status` | `ui/event-status.blade.php` | — | Estado de un evento de Eventbrite como etiqueta. |
| `x-ui.committee-chip` | `ui/committee-chip.blade.php` | — | Chip de comité con punto de tono por `id` o su icono Lucide validado; nunca imprime el HTML heredado. |
| `x-ui.file-list` | `ui/file-list.blade.php` | — | Archivos adjuntos con icono por tipo, tamaño legible y descarga; estado vacío sin archivos. |
| `x-ui.notification-list` | `ui/notification-list.blade.php` | — | Avisos de la persona con los no leídos destacados; un no leído se nombra con un único texto oculto («Evidencia rechazada, sin leer»). |
| `x-ui.message` | `ui/message.blade.php` | — | Un mensaje de un hilo con autor, rol, hora, estado y cuerpo. |
| `x-ui.form` | `ui/form.blade.php` | `busyForm` | Formulario con CSRF, método suplantado, estado ocupado contra el doble envío y resumen de errores enlazado que toma el foco; `bag` elige la bolsa de errores. |
| `x-ui.field` | `ui/field.blade.php` | `passwordField`, `charCount`, `selectField` | Control etiquetado (input, desplegable con buscador sobre un `<select>` nativo, textarea, contraseña con ojo) con pista, error, icono, prefijo o sufijo y valores del servidor. |
| `x-ui.choice` | `ui/choice.blade.php` | — | Casilla, radio o conmutador dentro de su etiqueta; solo CSS. Tras una validación fallida manda `old()`. |
| `x-ui.password-rules` | `ui/password-rules.blade.php` | `passwordRules` | Requisitos de una contraseña nueva marcados mientras se escribe; el botón de envío espera a que se cumplan todos. |
| `x-ui.checkbox-group` | `ui/checkbox-group.blade.php` | `checkboxGroup` | Casillas bajo una leyenda con pista y error comunes y un bloque dependiente de lo marcado. |
| `x-ui.upload` | `ui/upload.blade.php` | `upload` | Zona de subida sobre un `<input type="file">` real, lista de archivos con progreso y hidden por archivo subido. |
| `x-ui.picker` | `ui/picker.blade.php` | `picker` | Selector de personas, combobox con listbox y chips sobre un `<select multiple>` nativo, con listas predeterminadas. |
| `x-ui.icon-picker` | `ui/icon-picker.blade.php` | `iconPicker` | Un radio por icono Lucide de la lista curada, con vista previa y búsqueda por nombre y alias. |
| `x-ui.filter-bar` | `ui/filter-bar.blade.php` | — | Barra de filtros plegable (`<details>`) que cuenta los filtros activos, con un formulario GET, descarga y `keep` para reenviar el resto de la query string. |
| `x-ui.alert` | `ui/alert.blade.php` | — | Mensaje con tono, icono, título, cita, autoría y acciones; `role` `note`, `status` o `alert`; `dismissible`. |
| `x-ui.disclosure` | `ui/disclosure.blade.php` | — | `details` y `summary` nativos con título, icono, `extra` y acordeón exclusivo (`name`). |
| `x-ui.rich-text` | `ui/rich-text.blade.php` | — | HTML purificado con los estilos de prosa (`resources/css/components/prose.css`); el consumidor pasa `Html::clean()`. |
| `x-ui.agenda-editor` | `ui/agenda-editor.blade.php` | `agendaEditor` | Orden del día editable, serializado en el hidden `points_list` como JSON de cadenas. |
| `x-ui.minutes-editor` | `ui/minutes-editor.blade.php` | `minutesEditor` | Editor de puntos y acuerdos del acta, serializado en `points_json` conservando los ids. |
| `x-layout.base` | `layout/base.blade.php` | — | Documento HTML de la interfaz nueva; `entry` elige la entrada de Vite. |
| `x-layout.app` | `layout/app/index.blade.php` | — | Shell de la aplicación con barra lateral (raíl en tableta), topbar, avisos, `main`, pie, barra inferior y cajón. |
| `x-layout.app.sidebar`, `.topbar`, `.drawer` | `layout/app/` | — | Partes del shell; las usa solo `x-layout.app`. |
| `x-layout.public` | `layout/public.blade.php` | — | Páginas públicas (acceso, firma, bloqueo, errores) con la aurora intensa, cabecera flotante y pie compacto. |
| `x-pdf.document`, `x-pdf.table`, `x-pdf.section` | `pdf/` | — | Documentos para dompdf con hex planos y Helvetica; vista previa en `/_ui/pdf`. |

Los módulos sin componente propio son `resources/js/components/pageProgress.js` (la barra de progreso de página del layout base), `resources/js/components/lazyFrame.js` (los marcos del catálogo reciben su documento al entrar en el viewport), `resources/js/components/currentInView.js` (un `x-ui.tabs` con `x-data="currentInView"` desplaza su píldora hasta el enlace actual al cargar, como en el expediente de un estudiante) y `resources/js/alpine/announce.js` (anunciador compartido que usa `$store.notices` si existe). `x-ui.password-rules` no tiene sección en el catálogo; se ve en Mi perfil, en la gestión de usuarios y al restablecer la contraseña.

## Formularios

- `x-ui.form` pinta encima de sus campos el resumen de errores, un grupo con `tabindex="-1"` y `autofocus` que es lo único de una página que toma el foco al cargar; `focus-summary=false` lo deja sin foco. Cada enlace del resumen lleva al `ui_field_id` de su clave sin el último índice (`users.0` a `field-users`, `items.0.title` a `field-items-0-title`), una vez por destino.
- `bag` es el nombre de la bolsa de errores que lee el resumen (por defecto `default`) o una `MessageBag` ya hecha. Con `fields` el resumen lista solo esas claves, así que cada formulario de una página enseña los suyos; `fields` debe nombrar toda clave en la que el formulario pueda fallar, también las de los campos ocultos, o su error no sale en ningún sitio. Varios formularios en una página necesitan un `id` cada uno.
- Los campos leen la bolsa `default`; con otra bolsa el error de cada campo se pasa con la prop `error`.
- `x-ui.choice`. `checked` decide solo en la primera carga. En cuanto una validación fallida de cualquier formulario de la página devuelve la entrada, decide `old()` y una clave ausente es desmarcada (un radio la compara con `value`, una casilla `name[]` acepta arrays); dentro de un `x-ui.form` GET nunca se lee `old()`.
- `x-ui.field` y sus valores. `old()` gana a `value`. `value` admite un escalar, una fecha (`Y-m-d`, `H:i` o `Y-m-d\TH:i` para los tipos `date`, `time` y `datetime-local`; `d/m/Y H:i` en los demás), un enum respaldado o un `Stringable`; cualquier otro objeto falla en voz alta.
- El desplegable (`x-ui.field type="select"`) es un combobox con buscador (`selectField`, patrón APG de combobox editable con autocompletado de lista) sobre el `<select>` nativo, que conserva id, `name`, atributos y opciones, envía el valor y es el control sin JS. Un clic abre la lista con la opción elegida marcada y la caja vacía; escribir filtra sin distinguir mayúsculas ni acentos; flechas, Inicio y Fin mueven, Intro elige, Escape cierra y repone la etiqueta elegida, Tab sale. Cada elección escribe el select y dispara `input` y `change`, así que `x-model` y los formularios que lo siguen funcionan igual. La lista es un popover de la capa superior anclado a la caja (encima si abajo no cabe), de modo que ninguna tarjeta ni diálogo la recorta. `options` es `valor => etiqueta` o una lista de `{value, label, disabled}`; `placeholder` es la elección vacía y nunca se lista; `:submit="false"` deja el select sin `name` para una elección que solo mueve la página. En los specs se elige con `choose(combobox, 'Opción')` de `tests/e2e/fixtures/ui.js`.
- `x-ui.password-rules for="field-password" confirm="field-password-confirmation"` marca longitud, mayúscula, minúscula, número, símbolo y repetición con la misma regla que `App\Support\StrongPassword`, y deshabilita el botón de envío del formulario hasta que todas se cumplen; `optional` acepta contraseña y repetición vacías.

## Avisos y estado de la página

- `x-ui.notices` lee de la sesión `success`, `error`, `warning` y `light`. Un controlador añade un enlace a su flash con `->with('notice_link', ['href' => $url, 'action' => 'Ver la evidencia'])`; desde JS, `$dispatch('ui-notice', { tone, text, href, action })` o `Alpine.store('notices').push(...)`.
- `$store.notices`. `accepted` y `notice` se cierran solos a los 8 s con una barra del tiempo que queda; `rejected`, `pending` y todo aviso con enlace se quedan hasta que la persona los cierra (WCAG 2.2.1), porque una acción con tiempo se puede perder. Se ven a la vez tres como mucho; el resto espera en una cola y entra, anunciado y con su propia cuenta atrás, según se cierran los visibles. El puntero o el foco sobre un aviso detienen su cuenta (cada fuente por separado). Al cerrar, el foco pasa al aviso siguiente o al anterior y, sin ninguno, vuelve al control del que vino o a `main`.
- `$store.shortcuts`. Los atajos de una tecla (el `/` del buscador) vienen encendidos y la persona los apaga desde el menú de la cuenta o el cajón (WCAG 2.1.4); la elección se guarda en `localStorage` y sigue a las demás pestañas. Con los atajos apagados la tecla no hace nada y desaparecen la pista y `aria-keyshortcuts`. En Blade, `x-bind:checked="$store.shortcuts.enabled" x-on:change="$store.shortcuts.toggle()"` en un conmutador o `x-bind:aria-checked` en un ítem de menú.
- `pageProgress`. El layout base lleva una barra fina arriba que aparece cuando la página siguiente tarda más de 200 ms tras un clic en un enlace de la aplicación o un envío que nadie canceló, para que una respuesta lenta no parezca un clic perdido. Es decorativa (`aria-hidden`), se queda quieta con movimiento reducido y vuelve a ocultarse al regresar por el historial.

## Utilidades propias

`glass` y `glass-2` (cristal), `hue-*` y `committee-dot` (tono de comité decidido en el servidor), `orb-glow` (halo de un punto de estado), `halo-aurora` (halo de la cabecera de detalle), `no-scrollbar` (pestañas y filtros desplazables sin barra), `scrollbar-subtle` (barra fina con los colores de la interfaz que solo aparece con el puntero o el foco dentro, en la barra lateral y en los diálogos), `pb-safe-nav` y `bottom-safe` (hueco y posición de la barra inferior del teléfono con el área segura). Están en `resources/css/app.css`.

## Listados paginados en el servidor

Los listados que crecen se paginan en el servidor con `App\Support\ListPage`, que los controladores usan a través del trait `App\Http\Controllers\Concerns\PaginatesLists`.

- `searchList($query, $request, $columns)` aplica el parámetro `q` (recortado a 100 caracteres) sobre las columnas que elige cada pantalla: cada palabra tiene que aparecer en alguna columna, y una columna con puntos va por sus relaciones (`user.surname`, `users.name`, `committee.name`). `%` y `_` son texto, nunca comodines.
- `paginateList($rows, $request, $default = 25)` pagina una consulta, una relación o una colección ya ordenadas. El tamaño sale de `per_page` cuando es 25, 50 o 100; los enlaces conservan toda la query string (pestañas, filtros, orden, búsqueda y tamaño) y una página que no existe muestra la última.
- Los recuentos de las pestañas, los totales del pie (filas, horas) y los resúmenes se calculan sobre la consulta filtrada y buscada antes de paginarla (`$query->clone()->sum('hours')`), nunca sobre la página.
- En las vistas, `ListPage::url(['estado' => 'pendientes'], $list)` construye el enlace de una pestaña o de un orden con el resto de la query string y de vuelta en la primera página (un valor `null` quita su clave), y `ListPage::query(['comite', 'desde'])` da lo que la barra de filtros reenvía con `keep`, sin sus propios campos.

```php
$query = $this->searchList(Evidence::where('last', true), $request, ['title', 'user.name', 'user.surname']);
$hours = (float) $query->clone()->sum('hours');
$evidences = $this->paginateList($query->latest()->latest('id'), $request);
```

```blade
<x-ui.table caption="Evidencias del curso" :columns="$columns" :paginator="$evidences" filterable
    count-label="evidencias" count-singular="evidencia" filter-placeholder="Buscar evidencias">
    ...
    <x-slot:footer><span>{{ $evidences->total() }} evidencias · <x-ui.hours :value="$hours" size="sm" /></span></x-slot:footer>
</x-ui.table>
```

Con `paginator` la tabla cambia el filtro rápido por un formulario GET con `role="search"` que reenvía el resto de la query string, un botón para buscar, un enlace para quitar la búsqueda y el número de filas de todo el resultado (`count-singular` para una). El pie imprime lo que traiga su ranura (los totales) y, si hay más de una página o más de 25 filas, las filas por página y `x-ui.pagination` con «De la 26 a la 50 de 128» (`pagination-label` la nombra; por defecto «Paginación de» y el `caption`). El fragmento del paginador (`->fragment('evidencias')`) vuelve a la tabla desde los enlaces y la búsqueda. Se usa en las evidencias de gestión, del coordinador y del alumno, los alumnos, las asistencias de gestión y del coordinador de registro, los eventos, las reuniones de gestión, los bonos, los mensajes, el registro de actividad y los errores; la demostración del catálogo es «Listado paginado con búsqueda en el servidor», en la sección de tablas.

## Layouts

`x-layout.app` recibe `nav` (grupos de ítems con `label`, `href`, `icon`, `current`, `count`, `countLabel`, `mobile`), `mobileNav` (por defecto los cinco primeros marcados `mobile`), `breadcrumbs`, `deadline`, `user` (por defecto la persona autenticada), `notifications`, `search`, `subtitle`, `version` y `footerLinks`. Los componentes no leen roles ni rutas; esos arrays los compone por rol `App\View\Navigation`. Bajo `md` la barra lateral se oculta y aparecen la barra inferior y el cajón; entre `md` y `lg` la barra lateral es un raíl de 5 rem; desde `lg` mide 17 rem con rótulos y la tarjeta de plazo.

`x-layout.public` acepta `wide` (el `aside` a la izquierda del contenido desde `lg`), `intense`, `subtitle`, `version`, `showTheme`, `contentId` y `scene`; carga `public.js`. Una página de una columna pinta `x-ui.scene variant="sides"` en los márgenes salvo con `:scene="false"`; la de acceso pone la escena completa en su `aside`.

## Gramática Alpine permitida (build CSP)

La sonda de la sección «Sonda CSP» ejecuta estos casos en los tres proyectos de Playwright. Vale escribir en los atributos:

- `x-data="nombre"` con el nombre del módulo, además de `x-data` vacío para usar los stores y `x-data="{ open: true }"` para el estado trivial de `alert`.
- Llamadas a métodos con variables de `x-for`, `$event` y literales: `remove(index)`, `onItemKeydown($event, index)`, `announce('Copiado')`.
- `$store.notices.announce('texto')`, `$store.theme.is('dark')`, `$dispatch('ui-notice', { tone: 'accepted', text: 'Guardado' })`.
- Ternarios de literales en `x-bind` (`open ? 'true' : 'false'`), comparaciones en `x-show` y `x-bind:disabled`, `x-text="index + 1"`, asignaciones simples (`open = false`).
- `x-model` sobre propiedades de ítems, también anidadas, y `x-model.number`.
- Modificadores (`.window`, `.outside`, `.debounce.150ms`, `.enter.prevent`) y magics (`$refs`, `$nextTick`, `$id`).

Adaptaciones aprendidas en los lotes. `$root` y `$refs` se resuelven desde el elemento cuyo manejador se ejecuta, así que los editores guardan la raíz en `init()`; `x-show` muestra en el siguiente `requestAnimationFrame`, por lo que el foco se mueve tras `$nextTick` más un frame, y el popup del menú alterna `hidden` en lugar de `x-show`; Alpine no admite expresiones en `<iframe>`, de ahí el `div` de `lazyFrame`.

## De la interfaz anterior a la nueva

Lo de la columna «Antes» salió del repositorio en la v4.0.1, junto con AdminLTE, sus plugins y los estilos y scripts antiguos de `public/`.

| Antes | Ahora |
| --- | --- |
| `layouts/app`, menús y navbars, `x-li`, `x-lilogo`, `x-lilogout` | `x-layout.app` con `nav`, `breadcrumbs`, `search`, `bell`, `theme-switch`, `menu`, `footer`, `deadline`, `notices` |
| `home/main`, `auth/*`, `sign/*`, `block`, errores | `x-layout.public` + `card` + `form` + `field` + `choice switch` + `button block` + `count-chip` + `scene` + `dl` + `alert` + `empty` |
| DataTables y tablas secundarias | `table` (+ `tabs`, `pagination`, `time`, `hours`, `badge`, `button icon-only`) |
| `x-evidencestatus`, `x-attendeestatus`, `x-eventstatus`, `x-evidencecommittee`, `x-meetingcommittee`, `x-roles` | `evidence-status`, `attendee-status`, `event-status`, `committee-chip`, `badge variant="chip"` |
| `x-input`, selects y selectpicker de una opción, textareas, pares horas y minutos | `field` (el select con buscador; fieldset y legend compuestos en la vista) |
| dual listbox, select2, `getLista` | `picker` con listas predeterminadas |
| iCheck, form-check, «Recordarme» | `choice`, `checkbox-group` |
| bootstrap-select de tipo y modalidad | `segmented` |
| FilePond | `upload` |
| orden del día ordenable (`points_list`) | `agenda-editor` |
| editor de actas (`points_json`) | `minutes-editor` |
| bs-stepper | `timeline variant="steps"` |
| `x-buttonconfirm` y modales de confirmación | `dialog variant="alert"` con `<form method="post">`; `dialog` modal con `field textarea` |
| toasts de AdminLTE y flash | `notices` |
| `alert-info`, callouts, `x-slimreminder`, cuenta atrás | `deadline`, `alert` |
| widget-user del inicio y `x-profile` | `stat`, `card href`, `meter`, `dl`, `bar-list` |
| historial y motivos de rechazo | `timeline variant="log"`, `alert` con `quote` y `meta` |
| dropdown «Acciones» | `menu` |
| acordeones y collapse | `disclosure` |
| copiar URL | `button copy` |
| `Html::clean` + `{!! !!}` | `rich-text` |
| iconos de comité en la base de datos (HTML) | `icon-picker` + `committee-chip` (nombre Lucide) |
| `x-menumeeting` | `tabs` sobre el panel + `timeline steps` |
| plantillas dompdf | `pdf.document`, `pdf.table`, `pdf.section` |

## Iconos (Font Awesome a Lucide)

`fa-trash` → `trash-2`; `fa-pencil-alt`, `fa-edit` → `pen-line`; `fa-eye` → `eye`; `fa-eye-slash` → `eye-off`; `fa-file-pdf` → `file-text`; `fa-signature` → `file-signature`; `fa-scroll` → `scroll-text`; `fa-handshake` → `handshake`; `fa-clipboard-check` → `list-checks`; `fa-puzzle-piece` → `puzzle`; `fa-user-check` → `user-check`; `fa-calendar-alt` → `calendar`; `fa-users-cog` → `user-cog`; `fa-cogs` → `settings`; `fa-sitemap` → `network`; `fa-check-double` → `shield-check`; `fa-file-import` → `import`; `fa-file-export` → `download`; `fa-random` → `shuffle`; `fa-github` → `github`; `fa-envelope-open-text` → `message-square`; `fa-tachometer-alt` → `layout-dashboard`; `fa-id-badge` → `id-card`; `fa-sign-out-alt` → `log-out`; `fa-bars` → `menu`; `fa-copy` → `copy`; `fa-stopwatch` → `timer`; `fa-info-circle` → `info`; `fa-exclamation-circle`, `fa-exclamation-triangle` → `triangle-alert`; `fa-check-circle` → `circle-check`; `fa-times-circle` → `circle-x`; `fa-ellipsis-v` → `grip-vertical`; `fa-user` → `user-round`; `fa-lock` → `lock`; `fa-envelope` → `mail`; `fa-paper-plane` → `send`; `fa-plus` → `plus`; `fa-search` → `search`; `fa-bell` → `bell`; `fa-sync` → `rotate-ccw`; `fa-download` → `download`; `fa-cocktail` → `handshake`; `fa-hiking` → `ticket`; `fa-child` → `calendar-days`; `fa-screwdriver` → `key-round`; `fa-heart` desaparece.

Comités del seeder. `fa-user-tie` → `user-round`; `fa-file-signature` → `file-signature`; `fa-list-ol` → `list-ordered`; `fa-people-carry` → `hand-helping`; `fa-piggy-bank` → `piggy-bank`; `fa-euro-sign` → `euro`; `fa-warehouse` → `warehouse`; `fa-twitter` → `megaphone`; `fa-terminal` → `terminal`; desconocidos → `users`.

## Peso de los recursos compilados

Medido en la v4.0.1 con `npm run build`.

| Recurso | Tamaño | gzip |
| --- | --- | --- |
| `app-*.css` (toda la hoja de estilos) | 111,8 kB | 19,1 kB |
| `theme-*.js` (Alpine CSP y los módulos compartidos) | 87,2 kB | 28,5 kB |
| `app-*.js` (módulos solo de la aplicación) | 27,3 kB | 8,9 kB |
| `public-*.js` (entrada de las páginas públicas) | 0,3 kB | 0,2 kB |
| Fuentes (Plus Jakarta Sans y JetBrains Mono, variables) | 27,3 kB y 40,4 kB | — |

## Pruebas

- Render. `tests/Feature/Components/*Test.php` sobre `ComponentTestCase` (`render`, `assertCspSafe`, `assertUniqueIds`, `assertNoAutofocus`); `CspSafeViewsTest` recorre todas las vistas nuevas; `DesignSystemPageTest` cubre el catálogo y sus páginas; `ViewsCompileTest` compila todas las vistas Blade a PHP válido, de modo que ninguna apunta a un componente que ya no existe.
- Extremo a extremo. `tests/e2e` con Playwright en los proyectos `chromium` y `mobile` (Pixel 7); la fixture `tests/e2e/fixtures/ui.js` falla ante cualquier error de consola o mensaje de la CSP y ofrece `ui.axe()` (WCAG 2.2 AA), `ui.setTheme()`, `ui.section()`, `ui.noOverflow()` y `choose()` para el desplegable; `signIn()` de `tests/e2e/fixtures/auth.js` entra con los usuarios de `E2eSeeder`. `a11y.spec.js` audita el catálogo entero en claro y oscuro; cada página compuesta tiene su spec.
- Comandos en `README.md` (todo en Docker). El servidor de la demo es `php artisan serve`, de un solo hilo, así que Playwright se lanza con pocos `workers`.
