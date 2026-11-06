import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// The review of the coordinator: tabs by state, the rows of the committee and the accept and reject actions
test.describe('Revisión del coordinador', () => {
    test.beforeEach(async ({ page }) => {
        await signIn(page, 'coordinador');
    });

    test('enseña las evidencias del comité por estado', async ({ page, ui }) => {
        await page.goto('/coordinator/evidence/list/all');

        await expect(
            page.getByRole('heading', { level: 1, name: 'Revisar evidencias' }),
        ).toBeVisible();
        const table = page.getByRole('region', { name: 'Evidencias de Logística' });
        await expect(
            table.getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toBeVisible();
        await expect(table.getByText('Borrador de la memoria')).toHaveCount(0);
        await ui.noOverflow();

        const tabs = page.getByRole('navigation', { name: 'Filtrar evidencias por estado' });
        await tabs.getByRole('link', { name: /Aceptadas/ }).click();
        await expect(page).toHaveURL(/\/coordinator\/evidence\/list\/accepted$/);
        await expect(tabs.getByRole('link', { name: /Aceptadas/ })).toHaveAttribute(
            'aria-current',
            'page',
        );
        await expect(
            table.getByRole('link', { name: 'Cartelería del vestíbulo', exact: true }),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toHaveCount(0);
    });

    test('filtra las filas en el navegador', async ({ page }) => {
        await page.goto('/coordinator/evidence/list/all');

        await page.getByRole('searchbox', { name: 'Filtrar evidencias' }).fill('photocall');
        const table = page.getByRole('region', { name: 'Evidencias de Logística' });
        await expect(
            table.getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Cartelería del vestíbulo', exact: true }),
        ).toBeHidden();
    });

    test('enlaza cada fila con su evidencia', async ({ page }) => {
        await page.goto('/coordinator/evidence/list/pending');

        await expect(
            page.getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toHaveAttribute('href', /\/coordinator\/evidence\/view\/\d+$/);
    });

    test('pasa axe en claro y oscuro', async ({ page, ui }) => {
        await page.goto('/coordinator/evidence/list/all');
        expect(await ui.axe()).toEqual([]);

        await ui.setTheme('dark');
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });

    test('rechaza con motivo y vuelve a aceptar', async ({ page, ui }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        await page.goto('/coordinator/evidence/list/all');
        await page.getByRole('button', { name: 'Rechazar Atención en el registro' }).click();

        const dialog = page.getByRole('dialog', { name: 'Rechazar la evidencia' });
        await expect(dialog).toBeVisible();
        expect(await ui.axe()).toEqual([]);
        await dialog.getByLabel('Motivo del rechazo').fill('Falta el registro firmado del turno.');
        await dialog.getByRole('button', { name: 'Rechazar', exact: true }).click();

        await expect(page).toHaveURL(/\/coordinator\/evidence\/list\/rejected$/);
        await expect(
            page
                .getByRole('region', { name: 'Avisos' })
                .getByText('Evidencia rechazada con éxito.'),
        ).toBeVisible();
        const table = page.getByRole('region', { name: 'Evidencias de Logística' });
        await expect(
            table.getByRole('link', { name: 'Atención en el registro', exact: true }),
        ).toBeVisible();

        await table.getByRole('button', { name: 'Aceptar Atención en el registro' }).click();
        await expect(page).toHaveURL(/\/coordinator\/evidence\/list\/accepted$/);
        await expect(
            page.getByRole('region', { name: 'Avisos' }).getByText('Evidencia aceptada con éxito.'),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Atención en el registro', exact: true }),
        ).toBeVisible();
    });
});
