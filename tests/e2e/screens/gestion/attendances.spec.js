import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// Every attendance of the course and the events with their attendees, read only, for the president and the lecturer
test.describe('Asistencias de la gestión', () => {
    test('el presidente filtra las asistencias por estado, evento y texto', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/attendee/list');

        await expect(page.getByRole('heading', { level: 1, name: 'Asistencias' })).toBeVisible();
        const table = page.getByRole('region', { name: 'Asistencias del curso' });
        const lucia = table.getByRole('link', { name: 'Pérez Romero, Lucía', exact: true });
        await expect(lucia).toBeVisible();
        await expect(lucia).toHaveAttribute('href', /\/profiles\/view\/\d+$/);
        await expect(table.getByText('3,00 h')).toBeVisible();
        await expect(page.getByRole('link', { name: 'Añadir asistencia' })).toHaveCount(0);
        await ui.noOverflow();

        const tabs = page.getByRole('navigation', { name: 'Filtrar asistencias por estado' });
        await tabs.getByRole('link', { name: /Pendientes/ }).click();
        await expect(page).toHaveURL(/estado=pendientes/);
        await expect(table.getByText('No hay asistencias pendientes')).toBeVisible();
        await tabs.getByRole('link', { name: /Todas/ }).click();

        await page.getByText('Filtros de las asistencias').click();
        const select = page.getByLabel('Evento', { exact: true });
        const git = await select
            .locator('option', { hasText: 'Taller de Git' })
            .getAttribute('value');
        await select.selectOption(git);
        await page.getByRole('button', { name: 'Aplicar' }).click();
        await expect(page).toHaveURL(/evento=\d+/);
        await expect(lucia).toBeVisible();

        await page.getByRole('searchbox', { name: 'Filtrar asistencias' }).fill('torres');
        await expect(lucia).toBeHidden();
        await expect(
            table.getByRole('link', { name: 'Torres Gómez, Pablo', exact: true }),
        ).toBeVisible();
    });

    test('el profesor abre las asistencias de un evento desde la lista de eventos', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/attendee/list');

        const events = page.getByRole('region', { name: 'Eventos', exact: true });
        await expect(events.getByText('3,00 h')).toBeVisible();
        await events.getByRole('link', { name: 'Taller de Git', exact: true }).click();
        await expect(page).toHaveURL(/\/lecture\/attendee\/list\?evento=\d+$/);
        await expect(page.getByLabel('Evento', { exact: true })).toHaveValue(/\d+/);
        await ui.noOverflow();
    });

    for (const [who, path] of [
        ['profesor', '/lecture/attendee/list'],
        ['presidente', '/president/attendee/list'],
    ]) {
        test(`pasa axe en claro y oscuro en ${path}`, async ({ page, ui }) => {
            await signIn(page, who);
            await page.goto(path);
            await expect(
                page.getByRole('heading', { level: 1, name: 'Asistencias' }),
            ).toBeVisible();
            expect(await ui.axe()).toEqual([]);

            await ui.setTheme('dark');
            expect(await ui.axe()).toEqual([]);
            await ui.noOverflow();
        });
    }
});
