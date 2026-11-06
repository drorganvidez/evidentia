# Biblioteca de componentes

Referencia de la interfaz nueva de Evidentia (Fase 3 del rediseño). Todo lo que aparece aquí se ve en `/_ui`, el catálogo que se sirve solo en los entornos `local` y `testing`, y en sus páginas compuestas (`/_ui/shell`, `/_ui/public`, `/_ui/detail`, `/_ui/pdf`). El catálogo se renderiza con el shell real de la aplicación, así que es también su primer consumidor.

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
- Todo el comportamiento vive en módulos de `resources/js/components` descubiertos por `import.meta.glob` (el nombre del fichero es el `x-data`). `app.js` registra todos; `public.js` solo `busyForm`, `charCount`, `clipboard`, `countUp`, `dismissible`, `notices`, `pageProgress`, `passwordField`, `passwordRules` y `selectField` con los stores `theme` y `notices`.
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
| `x-ui.notices` | `ui/notices.blade.php` | `notices` + `$store.notices` | Pila de avisos (flash de sesión y eventos `ui-notice`) con las dos regiones vivas de la página; una por página. |
| `x-ui.bell` | `ui/bell.blade.php` | — | Campana de la topbar, enlace a la bandeja con el número de no leídos en su nombre. |
| `x-ui.search` | `ui/search.blade.php` | `shortcut` | Formulario GET de búsqueda con atajo de teclado (`/`); oculto en el teléfono salvo en el cajón. |
| `x-ui.count-chip` | `ui/count-chip.blade.php` | `countUp` | Cifra de la página de acceso que cuenta desde cero, con punto de tono y unidad. |
| `x-ui.table`, `x-ui.table.row`, `x-ui.table.cell` | `ui/table/` | `tableFilter`, `scrollRegion`, `rowLink` | Tabla de datos con orden y paginación en la URL, filtro rápido en el navegador, filas enlazadas y tarjetas en el teléfono. |
| `x-ui.tabs` | `ui/tabs.blade.php` | — | Filtros o pestañas por URL; el servidor filtra y el enlace actual lleva `aria-current`. |
| `x-ui.pagination` | `ui/pagination.blade.php` | — | Enlaces de un paginador de Laravel con ventana alrededor de la página actual; compacta en el teléfono. |
| `x-ui.stat` | `ui/stat.blade.php` | — | Tarjeta de cifra del panel con etiqueta, valor, unidad y pie; enlace con `href`. |
| `x-ui.bar-list` | `ui/bar-list.blade.php` | — | Lista de barras ya ordenada por el servidor, relativa a `max`. |
| `x-ui.dl`, `x-ui.dl.item` | `ui/dl/` | — | Pares término y valor como lista de definiciones real. |
| `x-ui.timeline`, `x-ui.timeline.item` | `ui/timeline/` | — | Pasos de un proceso (`variant="steps"`) o historial de eventos (`variant="log"`). |
| `x-ui.evidence-status` | `ui/evidence-status.blade.php` | — | Estado de una evidencia como etiqueta (`DRAFT`, `PENDING`, `ACCEPTED`, `REJECTED`, `BIN`), con el motivo en `title`. |
| `x-ui.attendee-status` | `ui/attendee-status.blade.php` | — | Asistencia de Eventbrite como etiqueta desde el literal almacenado. |
| `x-ui.event-status` | `ui/event-status.blade.php` | — | Estado de un evento de Eventbrite como etiqueta. |
| `x-ui.committee-chip` | `ui/committee-chip.blade.php` | — | Chip de comité con punto de tono por `id` o su icono Lucide validado; nunca imprime el HTML heredado. |
| `x-ui.file-list` | `ui/file-list.blade.php` | — | Archivos adjuntos con icono por tipo, tamaño legible y descarga; estado vacío sin archivos. |
| `x-ui.notification-list` | `ui/notification-list.blade.php` | — | Avisos de la persona con los no leídos destacados. |
| `x-ui.message` | `ui/message.blade.php` | — | Un mensaje de un hilo con autor, rol, hora, estado y cuerpo. |
| `x-ui.form` | `ui/form.blade.php` | `busyForm` | Formulario con CSRF, método suplantado, estado ocupado contra el doble envío y resumen de errores enlazado. |
| `x-ui.field` | `ui/field.blade.php` | `passwordField`, `charCount`, `selectField` | Control etiquetado (input, desplegable con buscador sobre un `<select>` nativo, textarea, contraseña con ojo) con pista, error, icono, prefijo o sufijo y valores del servidor. |
| `x-ui.choice` | `ui/choice.blade.php` | — | Casilla, radio o conmutador dentro de su etiqueta; solo CSS. |
| `x-ui.checkbox-group` | `ui/checkbox-group.blade.php` | `checkboxGroup` | Casillas bajo una leyenda con pista y error comunes y un bloque dependiente de lo marcado. |
| `x-ui.upload` | `ui/upload.blade.php` | `upload` | Zona de subida sobre un `<input type="file">` real, lista de archivos con progreso y hidden por archivo subido. |
| `x-ui.picker` | `ui/picker.blade.php` | `picker` | Selector de personas, combobox con listbox y chips sobre un `<select multiple>` nativo, con listas predeterminadas. |
| `x-ui.icon-picker` | `ui/icon-picker.blade.php` | `iconPicker` | Un radio por icono Lucide de la lista curada, con vista previa y búsqueda por nombre y alias. |
| `x-ui.filter-bar` | `ui/filter-bar.blade.php` | — | Barra de filtros plegable (`<details>`) que cuenta los filtros activos, con un formulario GET y descarga. |
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

Los módulos sin componente propio son `resources/js/components/lazyFrame.js` (los marcos del catálogo reciben su documento al entrar en el viewport), `resources/js/components/currentInView.js` (un `x-ui.tabs` con `x-data="currentInView"` desplaza su píldora hasta el enlace actual al cargar, como en el expediente de un estudiante) y `resources/js/alpine/announce.js` (anunciador compartido que usa `$store.notices` si existe).

## Layouts

`x-layout.app` recibe `nav` (grupos de ítems con `label`, `href`, `icon`, `current`, `count`, `countLabel`, `mobile`), `mobileNav` (por defecto los cinco primeros marcados `mobile`), `breadcrumbs`, `deadline`, `user` (por defecto la persona autenticada), `notifications`, `search`, `subtitle`, `version` y `footerLinks`. Los componentes no leen roles ni rutas; la Fase 4 añade el compositor de vista que construye esos arrays. Bajo `md` la barra lateral se oculta y aparecen la barra inferior y el cajón; entre `md` y `lg` la barra lateral es un raíl de 5 rem; desde `lg` mide 17 rem con rótulos y la tarjeta de plazo.

`x-layout.public` acepta `wide` (el `aside` a la izquierda del contenido desde `lg`), `intense`, `subtitle`, `version`, `showTheme` y `contentId`; carga `public.js`.

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

| Antes | Ahora |
| --- | --- |
| `layouts/app`, menús y navbars, `x-li`, `x-lilogo`, `x-lilogout` | `x-layout.app` con `nav`, `breadcrumbs`, `search`, `bell`, `theme-switch`, `menu`, `footer`, `deadline`, `notices` |
| `home/main`, `auth/*`, `sign/*`, `block`, errores | `x-layout.public` + `card` + `form` + `field` + `choice switch` + `button block` + `count-chip` + `dl` + `alert` + `empty` |
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

| Recurso | Tamaño | gzip |
| --- | --- | --- |
| `app-*.css` (toda la hoja de estilos) | 102,5 kB | 17,9 kB |
| `theme-*.js` (Alpine CSP y los módulos compartidos) | 76,4 kB | 25,2 kB |
| `app-*.js` (módulos solo de la aplicación) | 26,4 kB | 8,5 kB |
| `public-*.js` (entrada de las páginas públicas) | 0,2 kB | 0,2 kB |
| Fuentes (Plus Jakarta Sans y JetBrains Mono, variables) | 27,3 kB y 40,4 kB | — |

## Pruebas

- Render. `tests/Feature/Components/*Test.php` sobre `ComponentTestCase` (`render`, `assertCspSafe`, `assertUniqueIds`, `assertNoAutofocus`); `CspSafeViewsTest` recorre todas las vistas nuevas; `DesignSystemPageTest` cubre el catálogo y sus páginas.
- Extremo a extremo. `tests/e2e` con Playwright en los proyectos `chromium` y `mobile` (Pixel 7); la fixture `tests/e2e/fixtures/ui.js` falla ante cualquier error de consola o mensaje de la CSP y ofrece `ui.axe()` (WCAG 2.2 AA), `ui.setTheme()`, `ui.section()` y `ui.noOverflow()`. `a11y.spec.js` audita el catálogo entero en claro y oscuro; cada página compuesta tiene su spec.
- Comandos en `README.md` (todo en Docker). El servidor de la demo es `php artisan serve`, de un solo hilo, así que Playwright se lanza con pocos `workers`.
