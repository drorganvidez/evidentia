import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// Every evidence and every meeting of the course, for the lecturer and the president
test.describe('Evidencias y reuniones del curso', () => {
    test('el profesor filtra las evidencias por estado', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/evidence/list');

        await expect(page.getByRole('heading', { level: 1, name: 'Evidencias' })).toBeVisible();
        const table = page.getByRole('region', { name: 'Evidencias del curso' });
        await expect(
            table.getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toBeVisible();
        await ui.noOverflow();

        await page
            .getByRole('navigation', { name: 'Filtrar evidencias por estado' })
            .getByRole('link', { name: /Rechazadas/ })
            .click();
        await expect(page).toHaveURL(/estado=rechazadas/);
        await expect(
            table.getByRole('link', { name: 'Recogida de material', exact: true }),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toHaveCount(0);

        // The search runs on the server and keeps the tab
        const search = table.getByRole('searchbox', { name: 'Buscar evidencias' });
        await search.fill('nada parecido');
        await search.press('Enter');
        await expect(page).toHaveURL(/estado=rechazadas&q=nada\+parecido|q=nada\+parecido.*estado=rechazadas/);
        await expect(table.getByText('Ninguna evidencia coincide con la búsqueda')).toBeVisible();
        await expect(search).toHaveValue('nada parecido');
    });

    test('el presidente ve las reuniones con su acta', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/meeting/list');

        await expect(page.getByRole('heading', { level: 1, name: 'Reuniones' })).toBeVisible();
        const table = page.getByRole('region', { name: 'Reuniones del curso' });
        await expect(table.getByText('Reunión de logística')).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Acta de Reunión de logística' }),
        ).toHaveAttribute('href', /\/activity\/minutes\/\d+$/);
        await ui.noOverflow();
    });

    for (const path of ['/lecture/evidence/list', '/lecture/meeting/list']) {
        test(`pasa axe en claro y oscuro en ${path}`, async ({ page, ui }) => {
            await signIn(page, 'profesor');
            await page.goto(path);
            expect(await ui.axe()).toEqual([]);

            await ui.setTheme('dark');
            expect(await ui.axe()).toEqual([]);
            await ui.noOverflow();
        });
    }
});
