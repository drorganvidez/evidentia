import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import { ensureStudents } from './volume.js';

// The people of the course in pages of 10, searched and sorted on the server
test.describe('Alumnos en páginas', () => {
    test.beforeAll(async ({ playwright }, testInfo) => ensureStudents(playwright, testInfo));

    const isPhone = () => test.info().project.name === 'mobile';

    test('el profesor recorre las páginas con el ratón y el teclado', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/user/list');

        const table = page.getByRole('region', { name: 'Usuarios' });
        const pages = table.getByRole('navigation', { name: 'Paginación de Usuarios' });
        await expect(table.locator('tbody tr')).toHaveCount(10);
        await expect(table.getByText(/^\d{2} usuarios$/)).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'García López, Juan', exact: true }),
        ).toBeVisible();
        await ui.noOverflow();

        // The keyboard follows the next page
        const next = pages.getByRole('link', { name: 'Página siguiente' });
        await next.focus();
        await page.keyboard.press('Enter');
        await expect(page).toHaveURL(/\/lecture\/user\/list\?page=2$/);
        await expect(
            table.getByRole('link', { name: /^Volumen \d{2}, Ana$/ }).first(),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'García López, Juan', exact: true }),
        ).toHaveCount(0);

        if (isPhone()) {
            await pages.getByRole('link', { name: 'Primera página' }).click();
        } else {
            await pages.getByRole('link', { name: 'Ir a la página 1' }).click();
        }
        await expect(page).toHaveURL(/\/lecture\/user\/list\?page=1$/);
        await expect(
            table.getByRole('link', { name: 'García López, Juan', exact: true }),
        ).toBeVisible();
    });

    test('el orden, la búsqueda y el tamaño viajan juntos', async ({ page }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/user/list?sort=surname&dir=desc');

        const table = page.getByRole('region', { name: 'Usuarios' });
        await expect(table.getByRole('cell').getByRole('link', { name: /,/ }).first()).toHaveText(
            'Volumen 30, Ana',
        );

        await table
            .getByRole('group', { name: 'Filas por página' })
            .getByRole('link', { name: '50', exact: true })
            .click();
        await expect(page).toHaveURL(/sort=surname&dir=desc&per_page=50$/);
        await expect(table.getByRole('navigation', { name: /Paginación/ })).toHaveCount(0);

        const search = table.getByRole('searchbox', { name: 'Buscar usuarios' });
        await search.fill('volumen 1');
        await search.press('Enter');
        await expect(page).toHaveURL(/sort=surname&dir=desc&per_page=50&q=volumen\+1$/);
        // Volumen 01, 10 to 19 and 21
        await expect(table.getByText('12 usuarios', { exact: true })).toBeVisible();
        await expect(table.getByRole('cell').getByRole('link', { name: /,/ }).first()).toHaveText(
            'Volumen 21, Ana',
        );

        // The order keeps the search and the size; the phone cards have no column headers
        if (!isPhone()) {
            await table.getByRole('link', { name: 'UVUS', exact: true }).click();
            await expect(page).toHaveURL(/sort=username&dir=asc&per_page=50&q=volumen%201$/);
            await expect(search).toHaveValue('volumen 1');
        }
    });

    test('pasa axe en claro y oscuro con las páginas', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/user/list?page=2');
        expect(await ui.axe()).toEqual([]);

        await ui.setTheme('dark');
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });
});
