import { signIn } from '../../fixtures/auth.js';
import { choose, expect, test } from '../../fixtures/ui.js';

// The register coordinator: the token of Eventbrite, the events (also the ones created by hand) and the attendances
test.describe('Eventos y asistencias', () => {
    test.beforeEach(async ({ page }) => {
        await signIn(page, 'registro');
    });

    for (const [path, title] of [
        ['/registercoordinator/token', 'Eventbrite'],
        ['/registercoordinator/event/list', 'Eventos'],
        ['/registercoordinator/event/create', 'Nuevo evento'],
        ['/attendees', 'Asistencias'],
        ['/attendees/create', 'Añadir asistencia'],
        ['/attendees/import', 'Importar asistencias'],
    ]) {
        test(`${title} pasa axe en claro y oscuro`, async ({ page, ui }) => {
            await page.goto(path);
            await expect(page.getByRole('heading', { level: 1, name: title })).toBeVisible();
            await ui.noOverflow();
            expect(await ui.axe()).toEqual([]);

            await ui.setTheme('dark');
            expect(await ui.axe()).toEqual([]);
            await ui.noOverflow();
        });
    }

    test('el token vacío no se guarda', async ({ page }) => {
        await page.goto('/registercoordinator/token');
        await page.getByLabel('Token', { exact: true }).fill('');
        await page.getByRole('button', { name: 'Validar y guardar' }).click();
        await expect(page.getByText('Revisa el formulario')).toBeVisible();
    });

    test('importar asistencias sin hoja lo avisa', async ({ page }) => {
        await page.goto('/attendees');
        await page.locator('#attendee-import').click();
        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Importar asistencias');
        await page.locator('#attendee-import-form [type=submit]').click();
        await expect(
            page.getByText('Sube primero la hoja con las asistencias.').first(),
        ).toBeVisible();
    });

    test('lista los eventos y las asistencias del seed', async ({ page }) => {
        await page.goto('/registercoordinator/event/list');
        const events = page.getByRole('region', { name: 'Eventos del curso' });
        await expect(events.getByText('Taller de Git', { exact: true })).toBeVisible();
        await expect(events.getByText('Creado a mano').first()).toBeVisible();

        await page.goto('/attendees');
        const attendances = page.getByRole('region', { name: 'Asistencias del curso' });
        await expect(
            attendances.getByRole('link', { name: 'Pérez Romero, Lucía', exact: true }),
        ).toBeVisible();
        await page.getByRole('searchbox', { name: 'Buscar asistencias' }).fill('torres');
        await page.getByRole('searchbox', { name: 'Buscar asistencias' }).press('Enter');
        await expect(
            attendances.getByRole('link', { name: 'Pérez Romero, Lucía', exact: true }),
        ).toBeHidden();
    });

    test('crea un evento a mano, registra una asistencia y lo borra todo', async ({
        page,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        const notices = page.getByRole('region', { name: 'Avisos' });
        const name = `Taller de Docker ${Date.now()}`;

        // The event
        await page.goto('/registercoordinator/event/list');
        await page.getByRole('link', { name: 'Nuevo evento' }).click();
        await page.getByLabel('Nombre', { exact: true }).fill(name);
        await page.getByLabel('Inicio', { exact: true }).fill('2026-10-20T09:00');
        await page.getByLabel('Fin', { exact: true }).fill('2026-10-20T08:00');
        await page.getByRole('button', { name: 'Crear evento' }).click();
        await expect(page.getByText('Revisa el formulario')).toBeVisible();
        await page.getByLabel('Fin', { exact: true }).fill('2026-10-20T11:30');
        await page.getByRole('button', { name: 'Crear evento' }).click();
        await expect(notices.getByText('Evento creado con éxito.')).toBeVisible();

        const events = page.getByRole('region', { name: 'Eventos del curso' });
        await expect(events.getByText(name, { exact: true })).toBeVisible();
        await events.getByRole('link', { name: `Editar ${name}` }).click();
        await expect(page.getByLabel('Nombre', { exact: true })).toHaveValue(name);
        await page.getByLabel('Capacidad', { exact: true }).fill('30');
        await page.getByRole('button', { name: 'Guardar evento' }).click();
        await expect(notices.getByText('Evento actualizado con éxito.')).toBeVisible();

        await events.getByRole('button', { name: `Ocultar ${name}` }).click();
        await expect(notices.getByText('Evento ocultado correctamente.')).toBeVisible();
        await events.getByRole('button', { name: `Mostrar ${name}` }).click();
        await expect(notices.getByText('Evento visible nuevamente.')).toBeVisible();

        // An attendance to it
        await events.getByRole('link', { name: `Añadir una asistencia a ${name}` }).click();
        await expect(
            page.getByRole('heading', { level: 1, name: 'Añadir asistencia' }),
        ).toBeVisible();
        await expect(page.locator('select[name="event_id"] option:checked')).toContainText(name);
        await choose(
            page.getByRole('combobox', { name: 'Estudiante', exact: true }),
            'García López, Juan',
        );
        await page.getByRole('button', { name: 'Crear asistencia' }).click();
        await expect(notices.getByText('Asistencia creada con éxito.')).toBeVisible();

        await page.getByRole('searchbox', { name: 'Buscar asistencias' }).fill(name);
        await page.getByRole('searchbox', { name: 'Buscar asistencias' }).press('Enter');
        await page
            .getByRole('region', { name: 'Asistencias del curso' })
            .getByRole('link', { name: 'García López, Juan', exact: true })
            .click();
        await choose(
            page.getByRole('combobox', { name: 'Asistencia', exact: true }),
            'No asistido',
        );
        await page.getByRole('button', { name: 'Guardar cambios' }).click();
        await expect(notices.getByText('Asistencia actualizada con éxito.')).toBeVisible();

        // A event with attendances is not deleted
        await page.goto('/registercoordinator/event/list');
        await events.getByRole('button', { name: `Borrar ${name}` }).click();
        await page
            .getByRole('alertdialog', { name: 'Borrar el evento' })
            .getByRole('button', { name: 'Sí, borrar' })
            .click();
        await expect(
            notices.getByText('No se puede borrar un evento con asistencias.'),
        ).toBeVisible();

        await page.goto('/attendees');
        await page.getByRole('searchbox', { name: 'Buscar asistencias' }).fill(name);
        await page.getByRole('searchbox', { name: 'Buscar asistencias' }).press('Enter');
        await page
            .getByRole('region', { name: 'Asistencias del curso' })
            .getByRole('link', { name: 'García López, Juan', exact: true })
            .click();
        await page.getByRole('button', { name: 'Eliminar' }).click();
        await page
            .getByRole('alertdialog', { name: 'Eliminar la asistencia' })
            .getByRole('button', { name: 'Sí, eliminar' })
            .click();
        await expect(notices.getByText('Asistencia eliminada con éxito.')).toBeVisible();

        await page.goto('/registercoordinator/event/list');
        await events.getByRole('button', { name: `Borrar ${name}` }).click();
        await page
            .getByRole('alertdialog', { name: 'Borrar el evento' })
            .getByRole('button', { name: 'Sí, borrar' })
            .click();
        await expect(notices.getByText('Evento borrado con éxito.')).toBeVisible();
        await expect(events.getByText(name, { exact: true })).toHaveCount(0);
    });
});
