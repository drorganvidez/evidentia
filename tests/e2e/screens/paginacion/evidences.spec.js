import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import { ensureEvidences } from './volume.js';

// The evidences of the course for the management in pages of 10, searched on the server
test.describe('Evidencias del curso en páginas', () => {
    test.beforeAll(async ({ playwright }, testInfo) => ensureEvidences(playwright, testInfo));

    const isPhone = () => test.info().project.name === 'mobile';

    test('el profesor pasa de página con el ratón y vuelve con el teclado', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/evidence/list');

        const table = page.getByRole('region', { name: 'Evidencias del curso' });
        const pages = table.getByRole('navigation', { name: 'Paginación de Evidencias del curso' });
        await expect(pages).toBeVisible();
        await expect(table.locator('tbody tr')).toHaveCount(10);
        await expect(table.getByText(/^\d{2} evidencias ·/)).toBeVisible();
        await ui.noOverflow();

        await pages.getByRole('link', { name: 'Página siguiente' }).click();
        await expect(page).toHaveURL(/\/lecture\/evidence\/list\?page=2$/);
        if (isPhone()) {
            await expect(pages.getByText(/^Página 2 de \d$/)).toBeVisible();
        } else {
            await expect(
                pages.getByRole('link', { name: 'Página 2', exact: true }),
            ).toHaveAttribute('aria-current', 'page');
            await expect(pages.getByText(/^De la 11 a la \d+ de \d+$/)).toBeVisible();
        }

        const previous = pages.getByRole('link', { name: 'Página anterior' });
        await previous.focus();
        await page.keyboard.press('Enter');
        await expect(page).toHaveURL(/\/lecture\/evidence\/list\?page=1$/);
        await expect(table.locator('tbody tr')).toHaveCount(10);
    });

    test('la presidenta busca en el servidor sin perder la pestaña ni el tamaño', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/evidence/list?estado=aceptadas&per_page=50');

        const table = page.getByRole('region', { name: 'Evidencias del curso' });
        const sizes = table.getByRole('group', { name: 'Filas por página' });
        await expect(sizes.getByRole('link', { name: '50', exact: true })).toHaveAttribute(
            'aria-current',
            'true',
        );

        const search = table.getByRole('searchbox', { name: 'Buscar evidencias' });
        await search.fill('volumen 07');
        await search.press('Enter');
        await expect(page).toHaveURL(/estado=aceptadas&per_page=50&q=volumen\+07$/);
        await expect(table.locator('tbody tr')).toHaveCount(1);
        await expect(table.getByRole('link', { name: 'Volumen 07', exact: true })).toBeVisible();
        await expect(table.getByText('1 evidencia', { exact: true })).toBeVisible();
        await expect(table.getByRole('navigation', { name: /Paginación/ })).toHaveCount(0);

        // The tabs count the search and keep it
        const tabs = page.getByRole('navigation', { name: 'Filtrar evidencias por estado' });
        await expect(tabs.getByRole('link', { name: /Pendientes/ })).toContainText('0');
        await tabs.getByRole('link', { name: /Todas/ }).click();
        await expect(page).toHaveURL(/\?per_page=50&q=volumen%2007$/);
        await expect(table.getByRole('link', { name: 'Volumen 07', exact: true })).toBeVisible();
        await ui.noOverflow();

        await table.getByRole('link', { name: 'Quitar la búsqueda' }).click();
        await expect(page).toHaveURL(/\/president\/evidence\/list\?per_page=50$/);
        await expect(search).toHaveValue('');
    });

    test('en el teléfono salta a la primera y a la última página', async ({ page }) => {
        test.skip(!isPhone(), 'Solo en el teléfono');
        await signIn(page, 'profesor');
        await page.goto('/lecture/evidence/list');

        const pages = page
            .getByRole('region', { name: 'Evidencias del curso' })
            .getByRole('navigation', { name: 'Paginación de Evidencias del curso' });
        await pages.getByRole('link', { name: 'Última página' }).click();
        await expect(page).toHaveURL(/page=\d$/);
        await expect(pages.getByRole('link', { name: 'Página siguiente' })).toHaveCount(0);
        await pages.getByRole('link', { name: 'Primera página' }).click();
        await expect(page).toHaveURL(/page=1$/);
        await expect(pages.getByText(/^Página 1 de \d$/)).toBeVisible();
    });

    test('pasa axe en claro y oscuro con las páginas y la búsqueda', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/evidence/list?page=2&q=volumen');
        expect(await ui.axe()).toEqual([]);

        await ui.setTheme('dark');
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });
});
