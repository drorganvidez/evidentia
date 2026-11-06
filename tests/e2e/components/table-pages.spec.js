import { expect, test } from '../fixtures/ui.js';

// The table of the catalogue paginated and searched on the server: 128 evidences in pages of 25 by default
test.describe('Tabla paginada en el servidor', () => {
    const isPhone = () => test.info().project.name === 'mobile';

    test('pasa de página con el ratón y vuelve con el teclado', async ({ page, ui }) => {
        await page.goto('/_ui#paged-evidences');

        const table = page.locator('#paged-evidences');
        const pages = table.getByRole('navigation', {
            name: 'Paginación de Evidencias en páginas',
        });
        const position = isPhone()
            ? pages.getByText('Página 1 de 6', { exact: true })
            : pages.getByText('De la 1 a la 25 de 128', { exact: true });

        await expect(position).toBeVisible();
        await expect(table.locator('tbody tr')).toHaveCount(25);
        await expect(table.getByRole('link', { name: 'Boletín semanal 128' })).toBeVisible();
        await expect(pages.getByRole('link', { name: 'Página anterior' })).toHaveCount(0);

        await pages.getByRole('link', { name: 'Página siguiente' }).click();
        await expect(page).toHaveURL(/\/_ui\?page=2#paged-evidences$/);
        await expect(table.getByRole('link', { name: 'Boletín semanal 128' })).toHaveCount(0);
        await expect(table.getByRole('link', { name: 'Visita guiada 101' })).toBeVisible();
        if (isPhone()) {
            await expect(pages.getByText('Página 2 de 6', { exact: true })).toBeVisible();
        } else {
            await expect(pages.getByText('De la 26 a la 50 de 128', { exact: true })).toBeVisible();
            await expect(
                pages.getByRole('link', { name: 'Página 2', exact: true }),
            ).toHaveAttribute('aria-current', 'page');
        }

        // The keyboard reaches the previous page and follows it with Enter
        const previous = pages.getByRole('link', { name: 'Página anterior' });
        await previous.focus();
        await expect(previous).toBeFocused();
        await page.keyboard.press('Enter');
        await expect(page).toHaveURL(/\/_ui\?page=1#paged-evidences$/);
        await expect(table.getByRole('link', { name: 'Boletín semanal 128' })).toBeVisible();
        await ui.noOverflow();
    });

    test('salta a la última página y elige cuántas filas ver', async ({ page }) => {
        await page.goto('/_ui#paged-evidences');

        const table = page.locator('#paged-evidences');
        const pages = table.getByRole('navigation', {
            name: 'Paginación de Evidencias en páginas',
        });

        if (isPhone()) {
            // On the phone the numbers give way to the first and the last page
            await expect(pages.getByRole('link', { name: 'Ir a la página 2' })).toBeHidden();
            await pages.getByRole('link', { name: 'Última página' }).click();
            await expect(pages.getByText('Página 6 de 6', { exact: true })).toBeVisible();
            await expect(pages.getByRole('link', { name: 'Primera página' })).toBeVisible();
        } else {
            await expect(pages.getByRole('link', { name: 'Última página' })).toBeHidden();
            await pages.getByRole('link', { name: 'Ir a la página 6' }).click();
            await expect(
                pages.getByText('De la 126 a la 128 de 128', { exact: true }),
            ).toBeVisible();
        }
        await expect(page).toHaveURL(/page=6#paged-evidences$/);
        await expect(table.locator('tbody tr')).toHaveCount(3);
        await expect(pages.getByRole('link', { name: 'Página siguiente' })).toHaveCount(0);

        const sizes = table.getByRole('group', { name: 'Filas por página' });
        await expect(sizes.getByRole('link', { name: '25', exact: true })).toHaveAttribute(
            'aria-current',
            'true',
        );
        await sizes.getByRole('link', { name: '50', exact: true }).click();
        await expect(page).toHaveURL(/\/_ui\?per_page=50#paged-evidences$/);
        await expect(table.locator('tbody tr')).toHaveCount(50);
        await expect(sizes.getByRole('link', { name: '50', exact: true })).toHaveAttribute(
            'aria-current',
            'true',
        );
        await pages.getByRole('link', { name: 'Página siguiente' }).click();
        await expect(page).toHaveURL(/\/_ui\?per_page=50&page=2#paged-evidences$/);
    });

    test('busca en el servidor y conserva el tamaño', async ({ page, ui }) => {
        await page.goto('/_ui?per_page=50#paged-evidences');

        const table = page.locator('#paged-evidences');
        const search = table.getByRole('searchbox', { name: 'Buscar evidencias' });

        await search.fill('ruiz logística');
        await table.getByRole('button', { name: 'Buscar' }).click();
        await expect(page).toHaveURL(/\/_ui\?per_page=50&q=ruiz\+log%C3%ADstica#paged-evidences$/);
        await expect(search).toHaveValue('ruiz logística');
        await expect(table.getByText(/^\d+ evidencias$/).first()).toBeVisible();
        await expect(table.getByRole('navigation', { name: /Paginación/ })).toHaveCount(0);
        const rows = table.locator('tbody tr');
        const count = await rows.count();
        expect(count).toBeGreaterThan(0);
        for (const row of await rows.all()) {
            await expect(row).toContainText('Ruiz');
        }

        await search.fill('nada parecido');
        await search.press('Enter');
        await expect(table.getByText('Ninguna evidencia coincide con la búsqueda')).toBeVisible();

        await table.getByRole('link', { name: 'Quitar la búsqueda' }).click();
        await expect(page).toHaveURL(/\/_ui\?per_page=50#paged-evidences$/);
        await expect(search).toHaveValue('');
        await expect(rows).toHaveCount(50);
        await ui.noOverflow();
    });

    test('pasa axe en claro y oscuro', async ({ page, ui }) => {
        await page.goto('/_ui?page=3&q=a#paged-evidences');

        expect(await ui.axe('#paged-evidences')).toEqual([]);
        await ui.setTheme('dark');
        expect(await ui.axe('#paged-evidences')).toEqual([]);
    });
});
