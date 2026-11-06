import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import { auditThemes, confirmRemoval, mutatesData, notice, unique } from './helpers.js';

test.describe('Hojas de firmas', () => {
    // Two themes, two audits and a reload per screen
    test.slow();

    test.beforeEach(async ({ page }) => {
        await signIn(page, 'secretario');
    });

    test('la hoja cumple WCAG 2.2 AA en claro y oscuro', async ({ page, ui }) => {
        await page.goto('/secretary/meeting/manage/signaturesheet/list');
        await page.getByRole('link', { name: 'Hoja de firmas de logística', exact: true }).click();

        await expect(page.getByRole('table', { name: 'Firmas' })).toContainText('estudiante');
        await expect(page.getByRole('region', { name: 'Convocatoria' })).toContainText(
            'Convocatoria de logística',
        );
        await auditThemes(page, ui);
    });

    test('los formularios cumplen WCAG 2.2 AA en claro y oscuro', async ({ page, ui }) => {
        await page.goto('/secretary/meeting/manage/signaturesheet/create');
        await expect(
            page.getByRole('heading', { level: 1, name: 'Nueva hoja de firmas' }),
        ).toBeVisible();
        await auditThemes(page, ui);

        await page.goto('/secretary/meeting/manage/signaturesheet/list');
        // The row actions live on the desktop; the phone reaches them from the sheet itself
        await page.getByRole('link', { name: 'Hoja de firmas de logística', exact: true }).click();
        await page.getByRole('link', { name: 'Editar', exact: true }).click();
        await expect(page.getByLabel('Título', { exact: true })).toHaveValue(
            'Hoja de firmas de logística',
        );
        await auditThemes(page, ui);
    });

    test.describe('flujo completo', () => {
        mutatesData(test);

        test('crea, copia el enlace, renombra y elimina una hoja de firmas', async ({ page }) => {
            const title = unique('Hoja de prueba');

            await page.goto('/secretary/meeting/manage/signaturesheet/create');
            await page.getByLabel('Título', { exact: true }).fill(title);
            await page.getByRole('button', { name: 'Crear hoja de firmas' }).click();
            await expect(notice(page, 'Hoja de firmas creada con éxito.')).toBeVisible();

            await page.getByRole('link', { name: title, exact: true }).click();
            await expect(page.getByRole('heading', { level: 1, name: title })).toBeVisible();
            await expect(page.getByText('Sin firmas')).toBeVisible();
            await expect(page.getByRole('button', { name: 'Copiar enlace' })).toHaveAttribute(
                'data-copy',
                /\/sign\/\w{4}$/,
            );

            await page.getByRole('link', { name: 'Editar', exact: true }).click();
            await page.getByLabel('Título', { exact: true }).fill(`${title} renombrada`);
            await page.getByRole('button', { name: 'Guardar hoja de firmas' }).click();
            await expect(notice(page, 'Hoja de firmas actualizada con éxito.')).toBeVisible();

            await page.getByRole('link', { name: `${title} renombrada`, exact: true }).click();
            await confirmRemoval(
                page,
                page.getByRole('button', { name: 'Eliminar', exact: true }),
                'Eliminar la hoja de firmas',
                'Hoja de firmas eliminada con éxito.',
            );
            await expect(page.getByRole('row').filter({ hasText: title })).toHaveCount(0);
        });
    });
});
