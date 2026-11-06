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

test.describe('Listas predeterminadas', () => {
    // Two themes, two audits and a reload per screen
    test.slow();

    test.beforeEach(async ({ page }) => {
        await signIn(page, 'secretario');
    });

    test('la lista y el formulario cumplen WCAG 2.2 AA en claro y oscuro', async ({ page, ui }) => {
        await page.goto('/secretary/defaultlist/list');
        await expect(page.getByRole('heading', { level: 1, name: 'Listas' })).toBeVisible();
        await expect(page.getByRole('table', { name: 'Listas' })).toContainText(
            'Equipo de montaje',
        );
        await auditThemes(page, ui);

        await page.getByRole('link', { name: 'Equipo de montaje', exact: true }).click();
        await expect(page.getByLabel('Nombre')).toHaveValue('Equipo de montaje');
        await expect(
            page.getByRole('list', { name: 'Seleccionados' }).getByRole('listitem'),
        ).toHaveCount(2);
        await auditThemes(page, ui);
    });

    test.describe('flujo completo', () => {
        mutatesData(test);

        test('crea una lista eligiendo personas, la edita y la elimina', async ({ page }) => {
            const name = unique('Lista de prueba');

            await page.goto('/secretary/defaultlist/list');
            await page.getByRole('link', { name: 'Nueva lista' }).click();
            await page.getByLabel('Nombre').fill(name);
            await pickerReady(page);
            const people = page.getByRole('combobox', { name: 'Personas' });
            await people.fill('pérez');
            await people.press('Enter');
            await people.fill('garcía');
            await people.press('Enter');
            await people.press('Escape');
            await expect(
                page.getByRole('list', { name: 'Seleccionados' }).getByRole('listitem'),
            ).toHaveCount(2);
            await page.getByRole('button', { name: 'Guardar lista' }).click();

            await expectNotice(page, 'Lista creada con éxito.');
            const row = page.getByRole('row').filter({ hasText: name });
            await expect(row).toContainText('2');

            await row.getByRole('link', { name, exact: true }).click();
            await page
                .getByRole('list', { name: 'Seleccionados' })
                .getByRole('button')
                .first()
                .click();
            await page.getByRole('button', { name: 'Guardar lista' }).click();
            await expectNotice(page, 'Lista editada con éxito.');

            await confirmRemoval(
                page,
                page.getByRole('button', { name: `Eliminar ${name}` }),
                'Eliminar la lista',
                'Lista eliminada con éxito.',
            );
            await expect(page.getByRole('row').filter({ hasText: name })).toHaveCount(0);
        });
    });
});
