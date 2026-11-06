import { signIn } from '../../fixtures/auth.js';
import { choose, expect, test } from '../../fixtures/ui.js';

// The people of the course: filters, order and search in the URL, a new user and the mass actions
test.describe('Alumnos', () => {
    test('el profesor filtra por rol y ordena por apellidos', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/user/list');

        await expect(page.getByRole('heading', { level: 1, name: 'Alumnos' })).toBeVisible();
        const table = page.getByRole('region', { name: 'Usuarios' });
        await expect(
            table.getByRole('link', { name: 'Pérez Romero, Lucía', exact: true }),
        ).toBeVisible();
        await ui.noOverflow();

        await page.getByText('Filtros').click();
        await choose(page.getByRole('combobox', { name: 'Rol', exact: true }), 'Coordinador');
        await page.getByRole('button', { name: 'Aplicar' }).click();
        await expect(page).toHaveURL(/rol=COORDINATOR/);
        await expect(
            table.getByRole('link', { name: 'Navarro Ruiz, Carlos', exact: true }),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Pérez Romero, Lucía', exact: true }),
        ).toHaveCount(0);

        await page.getByRole('link', { name: 'Limpiar', exact: true }).click();
        await expect(page).toHaveURL(/\/lecture\/user\/list$/);

        if (test.info().project.name === 'chromium') {
            await table.getByRole('link', { name: 'Apellidos y nombre', exact: true }).click();
            await expect(page).toHaveURL(/sort=surname&dir=desc/);
            await expect(
                table.getByRole('cell').getByRole('link', { name: /,/ }).first(),
            ).toHaveText('Torres Gómez, Pablo');
        }
    });

    test('busca en el servidor y conserva el orden', async ({ page }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/user/list?sort=surname&dir=desc');

        const table = page.getByRole('region', { name: 'Usuarios' });
        const search = table.getByRole('searchbox', { name: 'Buscar usuarios' });
        await search.fill('navarro');
        await search.press('Enter');
        await expect(page).toHaveURL(/sort=surname&dir=desc&q=navarro/);
        await expect(
            table.getByRole('link', { name: 'Navarro Ruiz, Carlos', exact: true }),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Pérez Romero, Lucía', exact: true }),
        ).toHaveCount(0);
        await expect(table.getByText('1 usuario', { exact: true })).toBeVisible();

        await table.getByRole('link', { name: 'Quitar la búsqueda' }).click();
        await expect(page).toHaveURL(/\/lecture\/user\/list\?sort=surname&dir=desc$/);
        await expect(search).toHaveValue('');
    });

    test('pasa axe en claro y oscuro', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/user/list');
        expect(await ui.axe()).toEqual([]);

        await ui.setTheme('dark');
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });

    test('borrar todos pide confirmación y se puede cancelar', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/user/list');

        await page.getByRole('button', { name: 'Borrar todos' }).click();
        const alert = page.getByRole('alertdialog', { name: 'Borrar todos los usuarios' });
        await expect(alert).toBeVisible();
        expect(await ui.axe()).toEqual([]);
        await alert.getByRole('button', { name: 'Cancelar' }).click();
        await expect(alert).toBeHidden();
    });

    test('el presidente no borra a todos ni importa', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/user/list');

        await expect(page.getByRole('heading', { level: 1, name: 'Alumnos' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Borrar todos' })).toHaveCount(0);
        await expect(page.getByRole('link', { name: 'Importar', exact: true })).toHaveCount(0);
        await expect(page.getByText('Romero Organvídez, David')).toHaveCount(0);
        await ui.noOverflow();
    });

    test('añade un usuario y avisa de los errores', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        await signIn(page, 'profesor');
        await page.goto('/lecture/user/list');

        await page.getByRole('button', { name: 'Nuevo usuario' }).click();
        let dialog = page.getByRole('dialog', { name: 'Nuevo usuario' });
        await dialog.getByLabel('Nombre').fill('Ana');
        await dialog.getByRole('button', { name: 'Añadir usuario' }).click();

        // The dialog reopens with the summary of the errors
        dialog = page.getByRole('dialog', { name: 'Nuevo usuario' });
        await expect(dialog).toBeVisible();
        await expect(dialog.getByText('Revisa el formulario')).toBeVisible();

        const uvus = `ana${Date.now()}`;
        await dialog.getByLabel('Nombre').fill('Ana');
        await dialog.getByLabel('Apellidos').fill('Prueba Playwright');
        await dialog.getByLabel('Correo').fill(`${uvus}@e2e.test`);
        await dialog.getByLabel('UVUS').fill(uvus);
        await dialog.getByRole('button', { name: 'Añadir usuario' }).click();

        await expect(
            page.getByRole('region', { name: 'Avisos' }).getByText('Usuario creado con éxito'),
        ).toBeVisible();
        await page.getByRole('searchbox', { name: 'Filtrar usuarios' }).fill(uvus);
        await expect(
            page.getByRole('link', { name: 'Prueba Playwright, Ana', exact: true }).first(),
        ).toBeVisible();
    });
});
