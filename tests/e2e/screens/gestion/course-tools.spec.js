import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// The tools of the lecturer: integrity, export, import and the random pick of evidences
test.describe('Herramientas del curso', () => {
    test.beforeEach(async ({ page }) => {
        await signIn(page, 'profesor');
    });

    for (const [path, title] of [
        ['/lecture/integrity', 'Integridad'],
        ['/lecture/export', 'Exportar'],
        ['/lecture/import', 'Importar alumnos'],
        ['/randomize', 'Aleatorizar evidencias'],
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

    test('la integridad cambia entre evidencias y pruebas', async ({ page }) => {
        await page.goto('/lecture/integrity');
        await expect(
            page
                .getByRole('region', { name: 'Integridad de las evidencias' })
                .getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toBeVisible();

        await page
            .getByRole('navigation', { name: 'Qué comprobar' })
            .getByRole('link', { name: /Pruebas/ })
            .click();
        await expect(page).toHaveURL(/ver=pruebas/);
        await expect(page.getByRole('region', { name: 'Integridad de las pruebas' })).toBeVisible();
    });

    test('exporta el Excel con los bloques elegidos', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'La descarga solo en escritorio');

        await page.goto('/lecture/export');
        await page.getByRole('checkbox', { name: 'Bonos de horas' }).uncheck();
        const download = page.waitForEvent('download');
        await page.getByRole('button', { name: 'Descargar Excel' }).click();
        expect((await download).suggestedFilename()).toMatch(/^evidencias.*\.xlsx$/);
    });

    test('importar sin archivo lo avisa', async ({ page }) => {
        await page.goto('/lecture/import');
        await page.getByRole('button', { name: 'Importar alumnos' }).click();
        await expect(
            page
                .getByRole('region', { name: 'Avisos' })
                .getByText('Sube primero el archivo con los alumnos.'),
        ).toBeVisible();
    });

    test('sube la hoja, la quita y la vuelve a subir para importarla', async ({
        page,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las subidas solo en escritorio');

        // Not a real spreadsheet: the import reaches the reader, which rejects it without touching the people
        const sheet = {
            name: 'alumnos.xlsx',
            mimeType: 'application/octet-stream',
            buffer: Buffer.from('apellidos'),
        };
        await page.goto('/lecture/import');
        const files = page.getByRole('list', { name: 'Archivos' });
        await page.locator('input[type="file"]').setInputFiles([sheet]);
        await expect(files).toContainText('Subido', { timeout: 10_000 });
        await page.getByRole('button', { name: 'Quitar alumnos.xlsx' }).click();
        await expect(files).toBeHidden();

        await page.locator('input[type="file"]').setInputFiles([sheet]);
        await expect(files).toContainText('Subido', { timeout: 10_000 });
        await page.getByRole('button', { name: 'Importar alumnos' }).click();
        await expect(
            page.getByRole('region', { name: 'Avisos' }).getByText(/^Ocurrió un error/),
        ).toBeVisible();
    });

    test('aleatoriza tras confirmarlo', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        await page.goto('/randomize');
        await page.getByRole('button', { name: 'Aleatorizar' }).click();
        const alert = page.getByRole('alertdialog', { name: 'Aleatorizar evidencias' });
        await alert.getByRole('button', { name: 'Cancelar' }).click();
        await expect(alert).toBeHidden();

        await page.getByRole('button', { name: 'Aleatorizar' }).click();
        await alert.getByRole('button', { name: 'Sí, aleatorizar' }).click();
        await expect(
            page
                .getByRole('region', { name: 'Avisos' })
                .getByText('Evidencias aleatorizadas con éxito.'),
        ).toBeVisible();
        await expect(page.getByText('Evidencias elegidas')).toBeVisible();
    });
});
