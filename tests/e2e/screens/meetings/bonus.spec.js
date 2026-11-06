import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import {
    auditThemes,
    confirmRemoval,
    expectNotice,
    mutatesData,
    pickerReady,
    unique,
} from './helpers.js';

test.describe('Bonos', () => {
    // Two themes, two audits and a reload per screen
    test.slow();

    test.beforeEach(async ({ page }) => {
        await signIn(page, 'secretario');
    });

    test('la lista y el formulario cumplen WCAG 2.2 AA en claro y oscuro', async ({ page, ui }) => {
        await page.goto('/secretary/bonus/list');
        await expect(page.getByRole('heading', { level: 1, name: 'Bonos' })).toBeVisible();
        await expect(page.getByRole('table', { name: 'Bonos' })).toContainText(
            'Montaje del escenario',
        );
        await auditThemes(page, ui);

        await page.getByRole('link', { name: 'Montaje del escenario', exact: true }).click();
        await expect(page.getByLabel('Motivo')).toHaveValue('Montaje del escenario');
        await expect(
            page.getByRole('list', { name: 'Seleccionados' }).getByRole('listitem'),
        ).toHaveCount(1);
        await auditThemes(page, ui);
    });

    test.describe('flujo completo', () => {
        mutatesData(test);

        test('crea un bono con una lista predeterminada, lo edita y lo elimina', async ({
            page,
        }) => {
            const reason = unique('Bono de prueba');

            await page.goto('/secretary/bonus/list');
            await page.getByRole('link', { name: 'Nuevo bono' }).click();
            await page.getByLabel('Motivo').fill(reason);
            await page.getByLabel('Horas').fill('1.5');
            await pickerReady(page);
            await page.getByLabel('Lista predeterminada').selectOption({ index: 1 });
            await page.getByRole('button', { name: 'Cargar' }).click();
            await page.getByRole('button', { name: 'Sustituir' }).click();
            await page.getByRole('button', { name: 'Guardar bono' }).click();

            await expectNotice(page, 'Bono de horas creado con éxito.');
            const row = page.getByRole('row').filter({ hasText: reason });
            await expect(row).toContainText('1,50 h');

            await row.getByRole('link', { name: reason, exact: true }).click();
            await expect(
                page.getByRole('list', { name: 'Seleccionados' }).getByRole('listitem'),
            ).toHaveCount(2);
            await page.getByLabel('Horas').fill('2');
            await page.getByRole('button', { name: 'Guardar bono' }).click();
            await expectNotice(page, 'Bono editado con éxito.');
            await expect(page.getByRole('row').filter({ hasText: reason })).toContainText('2,00 h');

            await confirmRemoval(
                page,
                page.getByRole('button', { name: `Eliminar ${reason}` }),
                'Eliminar el bono',
                'Bono eliminado con éxito.',
            );
            await expect(page.getByRole('row').filter({ hasText: reason })).toHaveCount(0);
        });
    });
});
