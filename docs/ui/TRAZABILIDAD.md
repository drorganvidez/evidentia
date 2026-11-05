# Propuesta: registro de actividad (trazabilidad)

> Lo que pediste: que la nueva versión deje constancia histórica de todo lo que se hace, quién entra, cuándo, quién revisa, quién rechaza, quién escribe evidencias, quién crea reuniones, y con qué rol lo hace, porque al tener varios roles una misma persona se pierde la pista.
> Esto es lógica de negocio, así que va fuera del rediseño de la UI: propongo hacerlo en una fase propia, antes de migrar las vistas de gestión, para que la vista "Registro de actividad" (ya maquetada) tenga datos reales.

## Qué se anota

Una fila por acción en una tabla `activity_log`, **solo de inserción** (sin edición ni borrado desde la app; ni siquiera el profesor puede alterarla):

| Campo | Contenido |
|---|---|
| `id`, `created_at` | momento exacto (UTC en BD, mostrado en hora peninsular) |
| `user_id` | quién actúa (nulo en intentos de acceso fallidos) |
| `acting_role` | el rol con el que actuó: `STUDENT`, `COORDINATOR`, `SECRETARY`, `REGISTER_COORDINATOR`, `PRESIDENT`, `LECTURE` (se deduce de la ruta usada, no de los roles que tiene) |
| `action` | verbo cerrado: `login`, `login_failed`, `logout`, `password_reset`, `evidence.created`, `evidence.published`, `evidence.edited`, `evidence.reedited`, `evidence.accepted`, `evidence.rejected`, `evidence.deleted`, `proof.uploaded`, `proof.deleted`, `request.created/edited/deleted`, `sheet.created/edited/deleted`, `signature.signed`, `minutes.created/edited/deleted`, `bonus.created/edited/deleted`, `defaultlist.*`, `user.created`, `user.roles_changed`, `user.blocked`, `user.unblocked`, `user.password_changed_by_manager`, `committee.*`, `config.changed`, `import.users`, `export.*`, `eventbrite.*`, `randomize.run` |
| `subject_type`, `subject_id` | sobre qué (evidencia, acta, usuario…), con su título copiado para que sobreviva al borrado |
| `target_user_id` | a quién afecta cuando no coincide con el actor (el alumno cuya evidencia se rechaza, el usuario al que se cambian roles) |
| `details` | JSON con lo relevante y sin datos sensibles: motivo del rechazo, horas antes/después, roles antes/después, versión de la evidencia, nombre del archivo |
| `ip`, `user_agent` | de la petición; `ip` tal como llega tras el proxy de diversolab |
| `flags` | señales automáticas: `self_review` (un coordinador acepta o rechaza una evidencia propia), `after_deadline` (acción forzada fuera de plazo), `role_overlap` (actúa con un rol sobre algo que también le afecta como alumno) |

## Dónde se anota

- Un `Activity::record()` llamado desde los controladores en el punto en que el cambio ya está hecho (dentro de la misma transacción cuando la haya).
- Login, logout e intentos fallidos desde los eventos de autenticación de Laravel (`Login`, `Logout`, `Failed`), también para la firma pública de asistencia.
- Nada se anota desde las vistas ni desde el cliente.

## Quién lo ve

- **Profesorado y presidencia**: vista "Registro de actividad" con filtros por tipo, persona, rol, comité y fechas, exportable a CSV, y con los `flags` destacados.
- **Cada usuario**: su propio historial ("Mi actividad") y, en cada evidencia, acta o convocatoria, la pestaña *Historial* con las anotaciones que la afectan (es lo que ya enseña la maqueta del detalle).
- **Coordinadores y secretarios**: el historial de lo que pasa en su comité.

## Cuánto se guarda

Cinco cursos (configurable), con una orden `activity:prune` programada. Las anotaciones de accesos fallidos se purgan al año. Los datos personales que contiene (nombre, IP) se tratan como el resto de la aplicación.

## Regla adicional que propongo

Con el registro ya se ve la trampa; para cortarla de raíz: **nadie puede aceptar, rechazar ni firmar su propia evidencia, acta o asistencia**, aunque tenga el rol para hacerlo. El sistema lo impide y lo deja anotado con el flag `self_review` si lo intenta. Dime si quieres solo la señal o también el bloqueo.

## Coste

Una migración, un modelo, el helper, unas 40 llamadas en controladores y eventos, tres vistas y sus tests (incluido uno que recorre todas las rutas de escritura y comprueba que cada una deja anotación). Es una fase de trabajo aparte, sin riesgo para lo demás.

## Subsanación, dentro de plazo

Petición recogida el 6 de noviembre de 2026. Un alumno se equivoca a veces de comité al subir una evidencia. Eso, y en general cualquier error, debe poder corregirse sin tirar el trabajo, siempre dentro del plazo correspondiente.

- El alumno puede cambiar el comité de una evidencia propia mientras esté en borrador o rechazada (hoy ya puede, al editarla) y también mientras esté pendiente de revisión, sin perder las pruebas ni la descripción.
- El coordinador puede mover una evidencia pendiente de su comité a otro comité. Deja de verla él y pasa a la cola del coordinador de destino, con anotación en el registro de actividad y aviso al alumno.
- El mismo criterio para el resto de objetos. El secretario corrige una convocatoria, una hoja de firmas, un acta o un bono mientras el plazo de reuniones siga abierto; el coordinador de registro corrige asistencias mientras el plazo de asistencias siga abierto.
- Fuera de plazo, solo profesorado y presidencia, y queda anotado con el flag `after_deadline`.
- Cada corrección se anota en el registro de actividad con el antes y el después.

Entra en la fase de trazabilidad, junto al registro de actividad, porque es lógica de negocio y ambas cosas se apoyan en lo mismo.
