# Auditoría de la UI de Evidentia

> Fase 0 del rediseño. Fotografía de la interfaz a 5 de noviembre de 2026 (Laravel 13.40, PHP 8.4), antes de tocar nada.
> Los datos salen del código del repositorio y de una instancia local con datos de prueba.

## 1. Resumen

- **La plantilla no es AdminKit, es AdminLTE 3.1.0** (Bootstrap 4.6.0 + jQuery 3.6.0), copiada a mano en `public/plugins` y `public/dist`. El objetivo es el mismo: eliminarla entera.
- **Cada página autenticada carga 44 ficheros** (16 CSS y 28 JS) desde `layouts/app.blade.php`, uno detrás de otro y bloqueando el render, se usen o no: DataTables, Select2 *y* bootstrap-select, dos subidores de ficheros distintos (FilePond y Krajee FileInput), Summernote, dual listbox, Chart.js, jQuery UI, jQuery Knob, Sparklines, bs-stepper, iCheck, bootstrap-switch, Handlebars y Toastr.
- **6 recursos externos en cada página** (Google Fonts ×2, Ionicons, bootstrap-select y Popper desde jsDelivr/cdnjs). Sin internet, la app se ve rota. Los mismos 6 están en el layout de invitado, en las dos páginas de firma y en la de bloqueo.
- **El pipeline de Vite compila código muerto**: `resources/sass/app.scss` (Bootstrap 5 + una fuente de `fonts.bunny.net`) y `resources/js/app.js` (axios). Ninguna vista usa `@vite`, así que nada de eso llega al navegador; y `resources/css/app.css` (Tailwind) ni siquiera es una entrada. La imagen de producción hace `npm run build` para nada.
- **La imagen de producción usa Node 20.19**, que dejó de tener soporte el **30 de abril de 2026**. La LTS activa es **Node 26 "Lithium"** (LTS desde el 28/10/2026).
- **Sin favicon**: `public/favicon.ico` es el fichero vacío de Laravel (0 bytes) y ningún layout enlaza los PNG que sí hay (`favicon-32x32.png`, `apple-touch-icon.png`…).
- **El home pesa ~7 MB de fotos**: las tarjetas usan PNG de 1–3 MB como fondo (`deadline.png` 3,1 MB, `trabajo.png` 1,5 MB, `eventos.png` 1,2 MB, `proxima_reunion.png` 1,1 MB).
- **Los correos se construyen como páginas web completas**: `email/passwordreset` y `email/suggestionsmailbox` cargan AdminLTE, jQuery, Summernote y CDNs (que los clientes de correo descartan) y el logo apunta a una URL de producción escrita a mano (`https://evidentia.us.es/dist/img/logo_light.svg`).
- **22 vistas y 1 componente con `<script>` inline, y 107 atributos `style=""`**; DataTables se aplica a *todas* las tablas con `$('table').DataTable()` desde el layout; los mensajes flash se inyectan en JS con `{!! session('...') !!}` (se rompen con comillas o saltos de línea).
- **No hay métricas para presidencia/profesorado**: todos los roles comparten el mismo home (cuatro tarjetas con foto y plazos). Los listados de gestión son tablas planas.

## 2. Inventario de vistas

91 plantillas Blade: **57 vistas** (páginas, layouts, PDF y correos) y **34 componentes**.
Los roles salen de la matriz de `tests/Feature/PageAccessTest.php` (probada para cada página GET, cada rol e invitado).
"Todos" = cualquier usuario autenticado. "Patrones" se detectan en la propia plantilla; los documentos independientes (`home.main`, `sign.*`, `email.*`, `layouts.app`) cargan además todas las librerías del layout aunque no las usen.

| Vista | Ruta(s) | Roles | Layout | Patrones de UI | Líneas |
|---|---|---|---|---|---|
| `attendee.list` | /attendee/list | STUDENT | layouts.app | tabla, JS inline, iconos FA | 88 |
| `auth.login` | /login | invitado | home.main | formulario, iconos FA | 72 |
| `auth.passwords.reset` | /password/reset | invitado | home.main | formulario, iconos FA | 39 |
| `auth.passwords.update` | /password/update/{token} | invitado | home.main | formulario, iconos FA | 45 |
| `block` | /block | — (redirige) | documento propio | formulario, JS inline, iconos FA | 62 |
| `bonus.createandedit` | /secretary/bonus/create, /secretary/bonus/edit/{id} | SECRETARY | layouts.app | formulario, selectpicker/select2, dual listbox, JS inline, iconos FA | 168 |
| `bonus.list` | /secretary/bonus/list | SECRETARY | layouts.app | tabla, confirmación, JS inline, iconos FA | 129 |
| `config.config` | /lecture/config, /president/config | PRESIDENT, LECTURE | layouts.app | formulario, iconos FA | 112 |
| `defaultlist.createandedit` | /secretary/defaultlist/edit/{id}, /secretary/defaultlist/list/create | SECRETARY | layouts.app | formulario, dual listbox, iconos FA | 82 |
| `defaultlist.list` | /secretary/defaultlist/list | SECRETARY | layouts.app | tabla, confirmación, fechas relativas, iconos FA | 67 |
| `email.passwordreset` | correo de recuperación | — | documento propio | subida (fileinput), editor rico (Summernote), selectpicker/select2, dual listbox, JS inline | 136 |
| `email.suggestionsmailbox` | correo del buzón | — | documento propio | subida (fileinput), editor rico (Summernote), selectpicker/select2, dual listbox, JS inline | 120 |
| `eventbrite.attendee_create` | /attendees/create | REGISTER_COORDINATOR | layouts.app | formulario | 58 |
| `eventbrite.attendee_edit` | /attendees/{id}/edit | REGISTER_COORDINATOR | layouts.app | formulario | 63 |
| `eventbrite.attendee_list` | /attendees | REGISTER_COORDINATOR | layouts.app | tabla, formulario, modal, JS inline, iconos FA | 135 |
| `eventbrite.event_list` | /registercoordinator/event/list | REGISTER_COORDINATOR | layouts.app | tabla, formulario, iconos FA | 107 |
| `eventbrite.token` | /registercoordinator/token | REGISTER_COORDINATOR | layouts.app | formulario, iconos FA | 72 |
| `evidence.coordinator.list` | /coordinator/evidence/list/accepted, /coordinator/evidence/list/all, /coordinator/evidence/list/pending, /coordinator/evidence/list/rejected | COORDINATOR | layouts.app | tabla, estado evidencia, JS inline, fechas relativas, iconos FA | 121 |
| `evidence.createandedit` | /evidence/create, /evidence/edit/{id} | STUDENT; todos salvo LECTURE | layouts.app | formulario, modal, subida (FilePond), editor rico (Summernote), selectpicker/select2, JS inline, fechas relativas, iconos FA | 331 |
| `evidence.list` | /evidence/list | todos salvo LECTURE | layouts.app | tabla, estado evidencia, JS inline, fechas relativas, iconos FA | 119 |
| `evidence.view` | /coordinator/evidence/view/{id}, /evidence/view/{id} | COORDINATOR; STUDENT | layouts.app | tabla, estado evidencia, confirmación, fechas relativas, iconos FA | 159 |
| `home` | / | todos | layouts.app | iconos FA | 281 |
| `home.main` | layout de invitado | — | documento propio | tabla, modal, subida (fileinput), editor rico (Summernote), selectpicker/select2, dual listbox, JS inline, iconos FA | 216 |
| `importexport.export` | /lecture/export, /president/export | PRESIDENT, LECTURE | layouts.app | formulario, pestañas, iconos FA | 100 |
| `importexport.import` | /lecture/import | LECTURE | layouts.app | tabla, formulario, subida (FilePond), JS inline, iconos FA | 239 |
| `integrity.list` | /lecture/integrity, /president/integrity | PRESIDENT, LECTURE | layouts.app | tabla, pestañas, fechas relativas, iconos FA | 122 |
| `layouts.app` | layout autenticado | — | documento propio | subida (FilePond), subida (fileinput), editor rico (Summernote), selectpicker/select2, dual listbox, JS inline, iconos FA | 418 |
| `manage.committee_list` | /lecture/committee/list, /president/committee/list | PRESIDENT, LECTURE | layouts.app | tabla, formulario, modal, JS inline, iconos FA | 200 |
| `manage.evidence_list` | /lecture/evidence/list, /president/evidence/list | PRESIDENT, LECTURE | layouts.app | tabla, estado evidencia, fechas relativas, iconos FA | 70 |
| `manage.meeting_list` | /lecture/meeting/list, /president/meeting/list | PRESIDENT, LECTURE | layouts.app | tabla, fechas relativas, iconos FA | 71 |
| `manage.user_list` | /lecture/user/list, /president/user/list | PRESIDENT, LECTURE | layouts.app | tabla, formulario, modal, iconos FA | 204 |
| `manage.user_management` | /lecture/user/management/{id}, /president/user/management/{id} | PRESIDENT, LECTURE | layouts.app | formulario, selectpicker/select2, JS inline, iconos FA | 207 |
| `meeting.manage` | /secretary/meeting/manage | SECRETARY | layouts.app | iconos FA | 60 |
| `meeting.minutes_create_step1` | /secretary/meeting/manage/minutes/create/step1 | SECRETARY | layouts.app | formulario, selectpicker/select2, iconos FA | 128 |
| `meeting.minutes_create_step2` | /secretary/meeting/manage/minutes/create/step2 | SECRETARY | layouts.app | formulario, selectpicker/select2, iconos FA | 132 |
| `meeting.minutes_create_step3` | /secretary/meeting/manage/minutes/create/step3 | SECRETARY | layouts.app | formulario, modal, selectpicker/select2, dual listbox, JS inline, iconos FA | 802 |
| `meeting.minutes_edit` | /secretary/meeting/manage/minutes/edit/{id} | SECRETARY | layouts.app | formulario, modal, selectpicker/select2, dual listbox, JS inline, iconos FA | 594 |
| `meeting.minutes_list` | /secretary/meeting/manage/minutes/list | SECRETARY | layouts.app | tabla, formulario, modal, fechas relativas, iconos FA | 123 |
| `meeting.minutes_template` | PDF del acta (dompdf) | — | documento propio | tabla, CSS de impresión | 321 |
| `meeting.mylist` | /meeting/list | todos salvo LECTURE | layouts.app | tabla, JS inline, fechas relativas, iconos FA | 92 |
| `meeting.request_createandedit` | /secretary/meeting/manage/request/create, /secretary/meeting/manage/request/edit/{id} | SECRETARY | layouts.app | formulario, modal, selectpicker/select2, JS inline, iconos FA | 322 |
| `meeting.request_list` | /secretary/meeting/manage/request/list | SECRETARY | layouts.app | tabla, formulario, modal, fechas relativas, iconos FA | 118 |
| `meeting.request_template` | PDF de la convocatoria (dompdf) | — | documento propio | CSS de impresión | 90 |
| `meeting.signaturesheet_create` | /secretary/meeting/manage/signaturesheet/create | SECRETARY | layouts.app | formulario, selectpicker/select2, JS inline, iconos FA | 165 |
| `meeting.signaturesheet_edit` | /secretary/meeting/manage/signaturesheet/edit/{id} | SECRETARY | layouts.app | formulario, modal, selectpicker/select2, iconos FA | 119 |
| `meeting.signaturesheet_list` | /secretary/meeting/manage/signaturesheet/list | SECRETARY | layouts.app | tabla, formulario, modal, fechas relativas, iconos FA | 131 |
| `meeting.signaturesheet_view` | /secretary/meeting/manage/signaturesheet/view/{id} | SECRETARY | layouts.app | tabla, iconos FA | 110 |
| `message.mailbox` | /mailbox | todos | layouts.app | tabla, iconos FA | 128 |
| `profile.generalview` | /profiles/view/{id} | PRESIDENT, LECTURE | layouts.app | tabla, estado evidencia, fechas relativas, iconos FA | 72 |
| `profile.profile_evidence_view` | /profiles/view/{id_user}/evidence/{id_evidence} | PRESIDENT, LECTURE | layouts.app | estado evidencia, fechas relativas, iconos FA | 100 |
| `profile.view` | /profile | todos | layouts.app | formulario, modal, editor rico (Summernote), selectpicker/select2, pestañas, iconos FA | 174 |
| `randomize.randomize` | /randomize | LECTURE | layouts.app | formulario, iconos FA | 59 |
| `sign.sign` | /sign/{random_identifier} | invitado, todos | documento propio | formulario, subida (fileinput), editor rico (Summernote), selectpicker/select2, dual listbox, JS inline, iconos FA | 215 |
| `sign.sign_finish` | /sign/finish | — (redirige) | documento propio | subida (fileinput), editor rico (Summernote), selectpicker/select2, dual listbox, JS inline, iconos FA | 155 |
| `suggestionsmailbox` | /suggestionsmailbox | todos | layouts.app | formulario, editor rico (Summernote), selectpicker/select2, iconos FA | 102 |
| `updates.list` | /updates | todos | layouts.app | fechas relativas, iconos FA | 106 |
| `updates.list_error` | /updates (si falla GitHub) | todos | layouts.app | iconos FA | 29 |

### 2.1 Layouts y documentos independientes

| Documento | Uso | CSS | JS | Externos |
|---|---|---|---|---|
| `layouts/app.blade.php` | toda la zona autenticada (45 vistas lo extienden) | 16 | 28 | 6 |
| `home/main.blade.php` | invitado: login, recuperar y cambiar contraseña | 13 | 17 | 6 |
| `sign/sign.blade.php`, `sign/sign_finish.blade.php` | firma pública de asistencia (móvil, en la propia reunión) | 13 | 17 | 6 |
| `block.blade.php` | aviso de usuario bloqueado | 3 | 2 | 2 |
| `meeting/minutes_template`, `meeting/request_template` | PDF de acta y convocatoria (dompdf) | inline | — | 0 |
| `email/passwordreset`, `email/suggestionsmailbox` | correos (página web completa con JS) | 13 | 17 | 6 + logo absoluto |

`layouts.app` tiene 418 líneas: estructura AdminLTE, 9 menús por rol (`components/menus/*`), 3 barras superiores (`components/navbars/*`), inicialización global de DataTables, Summernote, sortable, Select2 y Toastr, y los avisos de sesión.

### 2.2 Componentes Blade (`resources/views/components`) y vistas que los usan

| Componente | Usos | Qué es |
|---|---|---|
| `input` | 14 | campo con etiqueta y error (fuerza `autofocus` siempre) |
| `li` | 10 | entrada de menú lateral |
| `menumeeting` | 12 | submenú de gestión de reuniones del secretario |
| `evidencestatus` | 6 | barra de progreso de colores para el estado de una evidencia |
| `id` | 6 | número con almohadilla |
| `evidencecommittee` | 5 | insignia de comité (icono FA en HTML guardado en BD) |
| `profile` | 5 | ficha resumen de un usuario (horas, biografía) |
| `buttonconfirm` | 4 | botón + modal de confirmación (borrar / editar rechazada) |
| `slimreminder` | 3 | cuenta atrás de un plazo |
| `textarea` | 3 | Summernote |
| `attendeestatus`, `evidencemanagecoordinator`, `lilogo`, `lilogout` | 2 | estado de asistencia, acciones del coordinador, logo, salir |
| `eventstatus`, `evidencelistcoordinator`, `evidenceoptionscoordinator`, `evidenceoptionsstudent`, `meetingcommittee`, `roles`, `textareasimple` | 1 | — |
| `menus/*` (9), `navbars/*` (3) | 1 | navegación por rol |

### 2.3 Patrones de UI que habrá que cubrir con la librería nueva

| Patrón | Dónde | Implementación actual |
|---|---|---|
| Tabla con búsqueda, orden y paginación | 23 vistas | DataTables 1.10 aplicado a *todas* las tablas; ordena por el texto de "hace 2 horas" |
| Estado de una evidencia | 6 vistas | barra de progreso de colores sin texto (`x-evidencestatus`) |
| Formulario con validación | 32 vistas | `x-input` (con `autofocus` en todos), `@error` a medias, errores en inglés |
| Editor de texto rico | descripción de evidencia, biografía, buzón | Summernote (jQuery) |
| Subida de ficheros | evidencias, importación | FilePond (y Krajee FileInput cargado en todas partes sin usarse) |
| Selector simple con búsqueda | 13 vistas propias (y todos los documentos independientes lo cargan) | bootstrap-select y Select2, a la vez |
| Selector múltiple de personas | actas, bonos, listas por defecto | bootstrap4-duallistbox + AJAX a `/gp/users/all` |
| Asistente por pasos | crear acta (3 pasos) | tres páginas con formularios encadenados |
| Editor de puntos y acuerdos | crear/editar acta, convocatoria | ~600–800 líneas de JS inline por vista, plantillas Handlebars |
| Modales de confirmación | 13 vistas | Bootstrap 4 modal (`x-buttonconfirm` y copias a mano) |
| Pestañas | perfil, exportaciones, integridad | Bootstrap 4 tabs |
| Avisos (flash) | layout | Toastr + `{!! session() !!}` dentro de JS |
| Fechas relativas | 15 vistas | `diffForHumans()` sin la fecha real accesible |
| Cuenta atrás de plazos | home, listados | `x-slimreminder` |
| Iconos | ~todas | Font Awesome 5 (fuente) + Ionicons por CDN; iconos de comité guardados como HTML en la BD |
| PDF | acta, convocatoria | dompdf con CSS propio |

## 3. Dependencias de la UI actual

### 3.1 Librerías cargadas (todas copiadas a mano en `public/`)

| Librería | Versión | Ruta | ¿Se usa de verdad? |
|---|---|---|---|
| AdminLTE | 3.1.0 | `public/dist/css/adminlte*.css`, `dist/js/adminlte*.js` | sí (estructura) |
| Bootstrap | 4.6.0 | `public/plugins/bootstrap` | sí |
| jQuery | 3.6.0 | `public/plugins/jquery` | sí (todo el JS) |
| jQuery UI | 1.12.1 | `public/plugins/jquery-ui` | solo `sortable` de una lista |
| DataTables (+ bs4) | 1.10.24 | `public/plugins/datatables*` | sí, en todas las tablas |
| Select2 (+ tema bs4) | 4.0.13 | `public/plugins/select2*` | parcialmente |
| bootstrap-select | 1.13.9 | **CDN jsDelivr** | sí (`.selectpicker`) |
| Popper | 1.14.7 | **CDN cdnjs** (y otra copia dentro de bootstrap.bundle) | duplicado |
| bootstrap4-duallistbox | 4.0.2 | `public/plugins/bootstrap4-duallistbox` | actas, bonos, listas |
| Summernote (bs4) | 0.8.18 | `public/plugins/summernote` | evidencias, biografía, buzón |
| FilePond (+2 plugins) | 4.26.1 | `public/dist/js/filepond*.js` | evidencias, importación |
| Krajee bootstrap-fileinput (+ piexif, sortable, purify, temas) | 5.0.9 | `public/dist/js/fileinput*`, `dist/themes` | **no** (cargado en todas partes) |
| Handlebars | 4.7.7 | `public/dist/js/handlebars-v4.7.7.js` | plantillas de puntos de actas |
| Toastr | 2.1.4 | `public/plugins/toastr` | avisos flash |
| Chart.js | 2.9.4 | `public/plugins/chart.js` | **no** |
| jQuery Knob, Sparklines | — | `public/plugins/jquery-knob`, `sparklines` | **no** |
| bs-stepper, bootstrap-switch, iCheck | — | `public/plugins/*` | **no** |
| Font Awesome Free | 5.15.3 | `public/plugins/fontawesome-free` (fuente de iconos) | sí |
| Ionicons | 2.0.1 | **CDN code.ionicframework.com** | no visible |
| Source Sans Pro, Nunito | — | **Google Fonts** | sí (tipografía) |

`public/plugins` ocupa 16 MB y `public/dist` 25 MB (mapas de AdminLTE de 2–4 MB e imágenes del home de 1–3 MB).
JS inline: 22 vistas y un componente tienen `<script>`; las dos del editor de actas superan las 500 líneas de JS cada una.

### 3.2 Imágenes y marca

- Logo: `public/dist/img/logo_light.svg` (y `logo.svg`). No hay versión para fondo claro/oscuro con criterio, ni símbolo cuadrado.
- Favicon: `favicon.ico` vacío; PNG de favicon/Android/Apple presentes pero sin enlazar; no hay `site.webmanifest`.
- Iconos de comité: HTML de Font Awesome guardado en la columna `committees.icon` y pintado con `{!! !!}`. Al quitar Font Awesome habrá que **mapear esos valores a iconos nuevos** (decisión pendiente: ver §8).

## 4. URLs externas que carga la app hoy

`grep -rnE "https?://" resources/ public/ --include=*.blade.php --include=*.css --include=*.js` devuelve más de 1.500 coincidencias, pero el 98 % son comentarios y licencias de las librerías copiadas. Las que **se cargan** son:

| Recurso | Dónde | Sustituir por |
|---|---|---|
| `fonts.googleapis.com` (Source Sans Pro, Nunito) | `layouts/app`, `home/main`, `sign/*`, `block`, `email/*` | fuentes autoalojadas (`@fontsource` / woff2 en el repo) |
| `code.ionicframework.com` (Ionicons 2.0.1) | mismos | nada (no se usa) |
| `cdn.jsdelivr.net` (bootstrap-select 1.13.9, CSS + JS) | `layouts/app`, `home/main`, `sign/*`, `email/*` | componente select propio |
| `cdnjs.cloudflare.com` (Popper 1.14.7) | mismos | nada (duplicado) |
| `fonts.bunny.net` (Nunito) | `resources/sass/app.scss` (código muerto) | se borra |
| `evidentia.us.es/dist/img/logo_light.svg` | `email/passwordreset` | `asset()` con `APP_URL` |
| `view.officeapps.live.com`, `docs.google.com/gview` | `public/dist/js/fileinput.js` (visores de vista previa) | se borra (Krajee no se usa) |

Enlaces `<a href>` legítimos que se pueden mantener: el repositorio de GitHub y la licencia GPL (pie), la consola de Eventbrite (`eventbrite/token`) y el buscador de iconos de Font Awesome (`manage/committee_list`, desaparece con el selector de iconos nuevo).
Aparte, `updates.list` consulta la API de GitHub **desde el servidor** en cada visita (no es una petición del navegador, pero conviene cachearla).

## 5. `package.json`, `vite.config.js` y `composer.json`

| Fichero | Situación | Propuesta (Fase 2) |
|---|---|---|
| `package.json` | Solo `dev` y `build`. Todo en `devDependencies`. Sin `engines`, sin `.nvmrc`. | Scripts `preview`, `lint`, `lint:js`, `lint:css`, `format`, `format:check`, `test:e2e`; `engines` y `.nvmrc` con Node 26 LTS. |
| `bootstrap`, `@popperjs/core`, `sass` | Solo los usa `resources/sass/app.scss`, que no carga ninguna vista. | Eliminar. |
| `axios` | Solo `resources/js/bootstrap.js`, que no carga ninguna vista (el JS real usa `$.ajax`). | Eliminar; `fetch` nativo. |
| `tailwindcss`, `@tailwindcss/vite` | Instalados, pero el plugin no está en `vite.config.js` y `resources/css/app.css` no es una entrada. | Usarlos de verdad (motor de utilidades con tokens propios). |
| `concurrently` | Lo usa el script `composer dev`. | Mantener o simplificar. |
| `blade-formatter` | Formateador documentado en el README; 5 avisos moderados de `npm audit` vienen de su árbol de `glob`. | Mantener; vigilar. |
| `vite.config.js` | Entradas `resources/sass/app.scss` y `resources/js/app.js` (muertas). | Entradas nuevas `resources/css/app.css` y `resources/js/app.js` (y quizá una de "firma pública"). |
| `composer.json` | `laravel/ui` solo aporta el login (`AuthenticatesUsers`). `barryvdh/laravel-dompdf` para los PDF. | Iconos SVG con `blade-ui-kit/blade-icons` + set elegido. `laravel/ui` puede quedarse (es lógica, no UI). |
| Ficheros de proyecto | Faltan `.editorconfig`, `.browserslistrc`, `eslint.config.js`, Prettier, Stylelint. | Añadirlos en Fase 2. |
| `docker/Dockerfile.prod` | Node 20.19 de Debian (sin soporte desde abril de 2026); `npm install` en vez de `npm ci`; el build no produce nada útil. | Etapa de build con Node 26 LTS (`npm ci && npm run build`) y copiar solo `public/build`. |

## 6. Flujos clave

### 6.1 Estudiante: subir una evidencia (lo hace desde el móvil)

`/evidence/list` → **Crear** (`/evidence/create`): título, horas + minutos, comité (bootstrap-select), descripción (Summernote), pruebas (FilePond) → *Guardar borrador* o *Publicar* → listado → `/evidence/view/{id}` (estado, motivos de rechazo, pruebas) → si la rechazan, *Editar* (ahora con lápiz) → vuelve a borrador → edita → publica.

Problemas: Summernote y FilePond no están pensados para móvil en esta configuración; la tabla del listado se corta en móvil (`table-responsive` en la propia tabla); el estado es una barra de color sin texto; "hace 1 semana" se ordena como texto; el formulario hace scroll solo por el `autofocus`; el historial de versiones existe en la BD pero no se muestra.

### 6.2 Coordinador: validar o rechazar

Menú *Gestionar evidencias* → `/coordinator/evidence/list/{all|pending|accepted|rejected}` → ver (`/coordinator/evidence/view/{id}`), descargar pruebas → **Aceptar** (formulario) o **Rechazar** (modal con motivo obligatorio).

Problemas: no hay acciones en bloque ni filtros más allá de las cuatro pestañas; el modal de rechazo no deja ver la evidencia; no se ve el historial ni los rechazos anteriores al revisar.

### 6.3 Secretario: reuniones, firmas, actas y bonos

Convocatoria (`request/create`, editor de orden del día) → hoja de firmas (código de 4 cifras) → en la reunión, los asistentes firman en `/sign/{código}` desde su móvil → **Crear acta** en tres pasos (convocatoria → hoja de firmas → datos, asistentes con dual listbox y editor de puntos/acuerdos) → PDF del acta. Además: listas por defecto, bonos de horas.

Problemas: el editor de puntos/acuerdos son textareas sin estilo y 600–800 líneas de JS inline por vista; el PDF del acta sale apelotonado; el dual listbox no escala con cientos de alumnos y no funciona bien en móvil; la página de firma pública carga toda la UI de AdminLTE para un formulario de dos campos.

### 6.4 Coordinador de registro: asistencias de Eventbrite

Token → listado de eventos (`/registercoordinator/event/list`, ocultar/mostrar) → *Cargar asistencias* → listado de asistencias con alta, edición y borrado manual → exportación a Excel.

### 6.5 Presidencia y profesorado: gestión y métricas

Configurar plazos, gestionar usuarios y roles, comités, listados globales de evidencias y reuniones, exportación final de horas, integridad, importación y aleatorizado (estos dos últimos, solo profesor).

Problemas: **no hay ninguna vista de métricas** (cuántas evidencias pendientes por comité, horas totales, plazos, alumnos sin actividad…); el home es el mismo que el del estudiante; los listados globales son tablas planas sin filtros por comité/estado.

## 7. Problemas de UX ya identificados

- `autofocus` en `x-input` y en ~30 campos más: la página salta al formulario de abajo al cargar.
- Fechas relativas que DataTables ordena como texto ("hace 1 semana" antes que "hace una hora").
- `table-responsive` puesto en el `<table>` (18 tablas): no ocupan el ancho de la tarjeta.
- Home con fotos de fondo de 1–3 MB y cero información útil para presidencia/profesorado.
- Editor de puntos y acuerdos del acta con textareas sin estilo, y PDF del acta apelotonado.
- Botones solo con icono y sin texto accesible (ver, editar, borrar, aceptar, rechazar…) en todos los listados; el texto existe solo como `<span>` vacío o oculto en pantallas pequeñas.
- Mensajes de validación en inglés (no hay `lang/es`).
- Sin favicon ni manifiesto.
- Sin modo oscuro, sin foco visible coherente, sin skip link ni landmarks.

## 8. Plan de migración por orden de impacto

| Orden | Bloque | Vistas | Por qué en este orden |
|---|---|---|---|
| 0 | **Fundaciones** (Fase 2) y **librería de componentes** + `/_ui` (Fase 3) | — | Todo lo demás se apoya en ellas. |
| 1 | **Shell + marca + auth**: layout nuevo, sidebar por rol, favicon/manifest, login, recuperar/cambiar contraseña, bloqueo | `layouts.app` (nuevo), `home.main`, `auth.*`, `block` | Primera impresión, todos los usuarios; elimina CDNs de la entrada. |
| 2 | **Firma pública** | `sign.sign`, `sign.sign_finish` | Se usa en el móvil, en la reunión, por todos; hoy carga 30 ficheros para dos campos. |
| 3 | **Evidencias del estudiante** | `evidence.list`, `evidence.createandedit`, `evidence.view`, `meeting.mylist`, `attendee.list` | El flujo más usado; subida desde el móvil. |
| 4 | **Validación del coordinador** | `evidence.coordinator.list`, `evidence.view` (rama coordinador) | Segundo flujo más usado. |
| 5 | **Dashboards por rol** (home) | `home` | Sustituye las fotos; vista de métricas para presidencia y profesorado. |
| 6 | **Reuniones y actas** | `meeting.*` (13), `defaultlist.*`, `bonus.*`, plantillas PDF | El editor de actas es el mayor bloque de JS a reescribir. |
| 7 | **Asistencias** | `eventbrite.*` | Pocas pantallas, un solo rol. |
| 8 | **Gestión** (presidencia/profesorado) | `manage.*`, `config.config`, `importexport.*`, `integrity.list`, `randomize.randomize`, `profile.generalview`, `profile.profile_evidence_view` | Mucha tabla; se beneficia de la tabla densa nueva. |
| 9 | **Perfil y varios** | `profile.view`, `suggestionsmailbox`, `message.mailbox`, `updates.*` | Bajo uso. |
| 10 | **Errores** | 403/404/419/500/503 (no existen: hoy salen las de Laravel) | Nuevas. |
| 11 | **Correos** | `email.*` | Reescritos con estilos inline y sin JS. |
| 12 | **Limpieza** | `public/plugins`, `public/dist` (AdminLTE, jQuery, Bootstrap, FA), `resources/sass`, deps npm muertas | Solo cuando ninguna vista los use; grep de verificación. |

Cada bloque se commitea completo y con la suite en verde; `tests/Feature/PageAccessTest.php` garantiza que ninguna página da 500 y que los permisos no cambian.

## 9. Decisiones que necesito de ti

1. **Iconos de comité**: hoy son HTML de Font Awesome en la BD. Propuesta: guardar un *nombre* de icono del set nuevo (con migración de datos que traduzca los actuales) y elegirlo con un selector visual. ¿OK?
2. **Editor de texto rico** (descripción de evidencias, biografía): ¿mantenemos texto con formato (sustituyendo Summernote por un editor ligero sin jQuery) o pasamos a texto plano con Markdown sencillo?
3. **Tablas**: ¿filtrado y paginación en servidor (más escalable, sin JS pesado) o en cliente como ahora con DataTables? Propongo servidor; con Livewire solo si lo quieres.
4. **Dashboard de presidencia/profesorado**: ¿qué métricas quieres ver primero? Propuesta: pendientes por comité, horas totales por tipo, alumnos sin actividad, plazos.
5. **Dual listbox de asistentes**: ¿lo sustituimos por un buscador con chips (escala a cientos de alumnos y funciona en móvil)?
