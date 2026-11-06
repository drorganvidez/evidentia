import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// The committees of the course: a new one with its icon, renaming one and removing an empty one
test.describe('Comités', () => {
    test.beforeEach(async ({ page }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/committee/list');
    });

    test('enseña los comités con sus registros', async ({ page, ui }) => {
        await expect(page.getByRole('heading', { level: 1, name: 'Comités' })).toBeVisible();
        const table = page.getByRole('region', { name: 'Comités del curso' });
        await expect(table.getByText('Logística', { exact: true })).toBeVisible();
        await expect(table.getByRole('button', { name: 'Eliminar Logística' })).toHaveCount(0);
        await ui.noOverflow();
    });

    test('pasa axe en claro y oscuro', async ({ page, ui }) => {
        expect(await ui.axe()).toEqual([]);

        await page.getByRole('button', { name: 'Editar Logística' }).click();
        await expect(page.getByRole('dialog', { name: 'Editar el comité' })).toBeVisible();
        expect(await ui.axe()).toEqual([]);

        await ui.setTheme('dark');
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });

    test('crea un comité con icono, lo renombra y lo elimina', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        const name = `Voluntariado ${Date.now()}`;
        await page.getByRole('button', { name: 'Nuevo comité' }).click();
        const create = page.getByRole('dialog', { name: 'Nuevo comité' });
        await create.getByLabel('Nombre').fill(name);
        await create.getByRole('searchbox', { name: 'Buscar icono' }).fill('ayuda');
        await create.getByRole('radio', { name: /^hand-helping/ }).check();
        await create.getByRole('button', { name: 'Crear comité' }).click();

        const notices = page.getByRole('region', { name: 'Avisos' });
        await expect(notices.getByText('Comité creado con éxito.')).toBeVisible();
        const table = page.getByRole('region', { name: 'Comités del curso' });
        await expect(table.getByText(name, { exact: true })).toBeVisible();

        await table.getByRole('button', { name: `Editar ${name}` }).click();
        const edit = page.getByRole('dialog', { name: 'Editar el comité' });
        await expect(edit.getByRole('radio', { name: /^hand-helping/ })).toBeChecked();
        await edit.getByLabel('Nombre').fill(`${name} y acogida`);
        await edit.getByRole('button', { name: 'Guardar' }).click();
        await expect(notices.getByText('Comités guardados con éxito.')).toBeVisible();
        await expect(table.getByText(`${name} y acogida`, { exact: true })).toBeVisible();

        await table.getByRole('button', { name: `Eliminar ${name} y acogida` }).click();
        await page
            .getByRole('alertdialog', { name: 'Eliminar el comité' })
            .getByRole('button', { name: 'Sí, eliminar' })
            .click();
        await expect(notices.getByText('Comité eliminado con éxito.')).toBeVisible();
        await expect(table.getByText(`${name} y acogida`, { exact: true })).toHaveCount(0);
    });
});
