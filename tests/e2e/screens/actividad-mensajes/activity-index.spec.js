import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import { audit } from './helpers.js';

// The activity log: the whole of it for the management, the committee for its coordinator
test.describe('Registro de actividad', () => {
    // several pages and two audits of axe per test, slow on the emulated phone
    test.slow();

    test('la gestión filtra, ve las señaladas y agrupa una acción', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/activity?per_page=25');

        await expect(
            page.getByRole('heading', { level: 1, name: 'Registro de actividad' }),
        ).toBeVisible();
        const table = page.getByRole('table', { name: 'Registro de actividad' });
        await expect(table.getByText('Comité editado')).toBeVisible();
        await expect(table.getByText('Inicio de sesión')).toHaveCount(0);

        const filters = page.locator('#activity-filters');
        if (!(await filters.evaluate((details) => details.open))) {
            await filters.locator('summary').click();
        }
        await filters.getByLabel('Incluir accesos y avisos').check();
        await filters.getByRole('button', { name: 'Aplicar' }).click();
        await expect(page).toHaveURL(/all=1/);
        await expect(table.getByText('Inicio de sesión').first()).toBeVisible();

        await page.goto('/activity?flagged=1');
        await expect(table.getByText('Autorrevisión bloqueada')).toBeVisible();
        await expect(table.getByText('Señalada, Autorrevisión', { exact: false })).toHaveCount(1);
        await expect(table.getByText('Comité editado')).toHaveCount(0);

        await page.goto('/activity?per_page=25');
        await table
            .getByRole('row')
            .filter({ hasText: 'Hoja de firmas creada' })
            .getByRole('link', { name: /en la misma acción/ })
            .click();
        await expect(page).toHaveURL(/request=/);
        await expect(table.getByText('Convocatoria creada')).toBeVisible();
        await expect(table.getByText('Hoja de firmas creada')).toBeVisible();
        await expect(page.getByRole('link', { name: 'Volver al registro' })).toBeVisible();
        await audit(page, ui);
    });

    test('la gestión descarga el registro en CSV', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'La descarga solo en escritorio');
        await signIn(page, 'profesor');
        await page.goto('/activity');

        const filters = page.locator('#activity-filters');
        if (!(await filters.evaluate((details) => details.open))) {
            await filters.locator('summary').click();
        }
        const download = page.waitForEvent('download');
        await filters.getByRole('button', { name: 'Descargar CSV' }).click();
        expect((await download).suggestedFilename()).toMatch(
            /^registro-actividad-\d{8}-\d{4}\.csv$/,
        );
    });

    test('el coordinador solo ve su comité', async ({ page, ui }) => {
        await signIn(page, 'coordinador');
        await page.goto('/activity');

        await expect(
            page.getByRole('heading', { level: 1, name: 'Actividad de Logística' }),
        ).toBeVisible();
        const table = page.getByRole('table', { name: 'Registro de actividad' });
        await expect(table.getByText('Evidencia rechazada').first()).toBeVisible();
        await expect(table.getByText('Comité editado')).toHaveCount(0);
        await expect(page.getByRole('button', { name: 'Descargar CSV' })).toHaveCount(0);
        await expect(page.locator('#field-committee')).toBeDisabled();
        await audit(page, ui);
    });
});
