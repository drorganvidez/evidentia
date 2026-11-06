# Progreso del rediseño

Resumen por fases para seguir el trabajo sin leer el historial completo. Cada fase termina con la suite de PHPUnit, el lint, la build y Playwright en verde y con una revisión adversaria (varios revisores por área y tres escépticos por hallazgo) cuyos hallazgos confirmados se corrigen antes de cerrar.

## Fase 0. Auditoría

Hecha. `AUDIT.md` recoge el estado de la UI anterior (AdminLTE, jQuery, recursos externos, estilos en línea, 24 listados con DataTables, etc.).

## Fase 1. Direcciones visuales

Hecha. Dos direcciones maquetadas (`direction-a.html`, `direction-b.html`, `login-a.html`); aprobada la A «Aurora» con cristal suave, el pie en una sola fila y la propuesta de trazabilidad de `TRAZABILIDAD.md`, que ahora incluye subsanación dentro de plazo, mensajes a los responsables y avisos.

## Fase 2. Fundaciones técnicas

Hecha (commits `b6aa479..5bc7b89`).

- Node 26 fijado (`.nvmrc`, `devEngines`, `.npmrc`), Vite 8, Tailwind CSS 4 en modo CSS-first con solo nuestros tokens (`resources/css/app.css`), fuentes autoalojadas (Plus Jakarta Sans y JetBrains Mono, OFL) con precarga y fallback métrico.
- Alpine en su build CSP, arrancado desde `resources/js/alpine/boot.js`; el tema claro/oscuro/sistema es un store compartido (`$store.theme`) y el modo sistema lo resuelve el CSS.
- Política de seguridad de contenidos estricta con nonce por petición (`App\Http\Middleware\ContentSecurityPolicy`, `config/csp.php`), global y en modo informe mientras queden vistas antiguas; `X-Frame-Options`, endpoint de informes con su canal de log y proxies de confianza.
- Layout base (`<x-layout.base>`), fondo aurora, iconos Lucide con blade-icons y la página `/_ui` con los tokens (solo en local y testing).
- Cadena de calidad del front (ESLint, Prettier, Stylelint, blade-formatter), `.bladeignore` con las vistas que siguen en el layout antiguo, Playwright (escritorio y móvil) con comprobación de CSP y de desbordes a 320 px.
- Imagen de producción multietapa sin Node, volumen `public_build` compartido con nginx, servicio `vite` en desarrollo, CI con jobs de backend, front y E2E, Dependabot.
- Revisión adversaria: 22 hallazgos confirmados y corregidos (contraste AA, desborde a 320 px, CSP en todas las respuestas, proxies, Docker, documentación).

## Fase 3. Biblioteca de componentes

Hecha la implementación (pendiente la revisión adversaria de cierre). Plan diseñado con tres inventarios (vistas actuales, mockups, buenas prácticas), tres propuestas, dos jueces y una síntesis; implementado en seis lotes (fundamentos y primitivas; shell, navegación y avisos; datos y listados; formularios; detalle, editores e impresión; integración), los cuatro intermedios en paralelo sobre ramas propias e integrados en `main` con sus commits atómicos.

- 57 componentes Blade en `resources/views/components/ui`, `layout` y `pdf`, documentados en `COMPONENTS.md` y expuestos en `/_ui` (49 secciones) con cuatro páginas compuestas, `/_ui/shell`, `/_ui/public`, `/_ui/detail` y `/_ui/pdf`.
- Dos layouts, `x-layout.app` (barra lateral, raíl en tableta, topbar, cajón y barra inferior en el teléfono, avisos, pie con versión) y `x-layout.public` (acceso, firma, bloqueo y errores con la entrada ligera `public.js`). El propio catálogo se renderiza con el shell.
- Comportamiento en módulos Alpine (build CSP) descubiertos por nombre de fichero, con una sonda que verifica la gramática permitida; sin estilos en línea ni manejadores en atributos en ninguna vista nueva.
- Tests de render por componente (PHPUnit, 642 tests en total con los de la aplicación), `CspSafeViewsTest`, `DesignSystemPageTest` (catálogo, páginas, ids únicos) y Playwright en escritorio y móvil con la fixture que falla ante cualquier error de consola, axe (WCAG 2.2 AA) en claro y oscuro sobre todo el catálogo y las páginas, y specs de interacción (diálogos, menús, avisos, plazos, tablas, formularios, subida, selector de personas e iconos, editores, acceso y detalle).

## Fases siguientes

- Trazabilidad, mensajes, avisos y subsanación (lógica de negocio con tests).
- Fase 4, migración de todas las vistas al nuevo diseño, cada una con su prueba de extremo a extremo en escritorio y móvil.
- Fase 5, calidad (Lighthouse, accesibilidad, barrido completo).
- Detalles finales, entre ellos una ilustración propia para la pantalla de acceso.
