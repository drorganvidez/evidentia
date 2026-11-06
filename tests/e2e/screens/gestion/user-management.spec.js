import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

async function openCompanero(page) {
    await signIn(page, 'profesor');
    await page.goto('/lecture/user/list');
    await page.getByRole('link', { name: 'Gestionar García López, Juan' }).click();
    await expect(page.getByRole('heading', { level: 1, name: 'García López, Juan' })).toBeVisible();
}

// Management of one person: data, access, roles with the committee that coordinators and secretaries need
test.describe('Gestión de un usuario', () => {
    test('el comité aparece solo con coordinador o secretario', async ({ page, ui }) => {
        await openCompanero(page);

        const committee = page.getByLabel('Comité');
        await expect(committee).toBeHidden();
        await page.getByRole('checkbox', { name: 'Secretario' }).check();
        await expect(committee).toBeVisible();
        await page.getByRole('checkbox', { name: 'Secretario' }).uncheck();
        await expect(committee).toBeHidden();
        await expect(
            page.getByRole('switch', { name: 'Permitir acceso a la aplicación' }),
        ).toBeChecked();
        await ui.noOverflow();
    });

    test('pasa axe en claro y oscuro', async ({ page, ui }) => {
        await openCompanero(page);
        expect(await ui.axe()).toEqual([]);

        await ui.setTheme('dark');
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });

    test('pide el comité y guarda los cambios', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        await openCompanero(page);
        await page.getByRole('checkbox', { name: 'Secretario' }).check();
        await page.getByRole('button', { name: 'Guardar cambios' }).click();

        await expect(page.getByText('Revisa el formulario')).toBeVisible();
        await expect(page.getByText('Comité no válido.').first()).toBeVisible();
        await expect(page.getByRole('checkbox', { name: 'Secretario' })).toBeChecked();
        await expect(page.getByLabel('Comité')).toBeVisible();

        await page.getByRole('checkbox', { name: 'Secretario' }).uncheck();
        await page.getByRole('button', { name: 'Guardar cambios' }).click();

        await expect(page).toHaveURL(/\/lecture\/user\/list/);
        await expect(
            page.getByRole('region', { name: 'Avisos' }).getByText('Usuario actualizado con éxito'),
        ).toBeVisible();
    });
});
