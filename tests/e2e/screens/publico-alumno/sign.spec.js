import { expect, test } from '../../fixtures/ui.js';

async function audit(page, ui, selector = null) {
    await ui.noOverflow();

    for (const theme of ['light', 'dark']) {
        await ui.setTheme(theme);
        expect(await ui.axe(selector), `axe en ${theme}`).toEqual([]);
    }
}

test.describe('Firma pública', () => {
    test('enseña la convocatoria y un error de credenciales vuelve con aviso', async ({
        page,
        ui,
    }) => {
        await page.goto('/sign/7391');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Firmar asistencia');
        await expect(page.getByText('Convocatoria de logística')).toBeVisible();
        await audit(page, ui, 'header, main');

        await page.getByLabel('Correo electrónico').fill('companero@e2e.test');
        await page.getByLabel('Contraseña', { exact: true }).fill('no-es-la-clave');
        await page.getByRole('button', { name: 'Firmar' }).click();

        await expect(page.getByText('Las credenciales no son válidas.').first()).toBeVisible();
    });

    test('una firma correcta termina con su resultado', async ({ page, ui }, info) => {
        test.skip(info.project.name !== 'chromium', 'Firma una vez por ejecución');

        await page.goto('/sign/7391');
        await page.getByLabel('Correo electrónico').fill('registro@e2e.test');
        await page.getByLabel('Contraseña', { exact: true }).fill('evidentia');
        await page.getByRole('button', { name: 'Firmar' }).click();

        await expect(page).toHaveURL(/\/sign\/finish$/);
        // A first run registers it; a later one against the same seed finds it already signed
        await expect(page.getByRole('status').filter({ hasText: 'Firma' })).toBeVisible();
        await ui.noOverflow();
        expect(await ui.axe('header, main')).toEqual([]);
    });
});
