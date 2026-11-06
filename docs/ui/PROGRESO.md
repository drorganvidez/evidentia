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

En curso. Plan diseñado con tres inventarios (vistas actuales, mockups, buenas prácticas), tres propuestas, dos jueces y una síntesis; se implementa por lotes con tests de render y de extremo a extremo y se enseña en `/_ui`.

## Fases siguientes

- Trazabilidad, mensajes, avisos y subsanación (lógica de negocio con tests).
- Fase 4, migración de todas las vistas al nuevo diseño, cada una con su prueba de extremo a extremo en escritorio y móvil.
- Fase 5, calidad (Lighthouse, accesibilidad, barrido completo).
- Detalles finales, entre ellos una ilustración propia para la pantalla de acceso.
