import { signIn } from '../../fixtures/auth.js';
import { choose, expect, test } from '../../fixtures/ui.js';
import { auditThemes } from './helpers.js';

// Search over the minutes of every committee, open to anyone signed in
test.describe('Buscar acuerdos', () => {
    test.slow();

    test('el alumno busca un acuerdo por palabras y abre su acta', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/');
        // the menu link (folded in a drawer on the phone)
        await expect(page.locator('a[href$="/meeting/search"]').first()).toBeAttached();
        await page.goto('/meeting/search');
        await expect(
            page.getByRole('heading', { level: 1, name: 'Buscar acuerdos' }),
        ).toBeVisible();

        const search = page.getByRole('searchbox', { name: 'Palabras o identificador' });
        await search.fill('responsable');
        await search.press('Enter');
        const table = page.getByRole('table', { name: 'Acuerdos' });
        await expect(table).toContainText('ISD-1');
        await expect(table).toContainText('Reunión de logística');
        await auditThemes(page, ui);

        await table.getByRole('link', { name: /Cada equipo nombra un responsable/ }).click();
        await expect(page.getByRole('heading', { level: 1 })).toContainText('Reunión de logística');
    });

    test('el profesor busca reuniones y ve todos sus acuerdos', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/meeting/search?tab=reuniones');

        const search = page.getByRole('searchbox', { name: 'Palabras clave' });
        await search.fill('tareas');
        await search.press('Enter');
        const table = page.getByRole('table', { name: 'Reuniones' });
        await expect(table).toContainText('Reunión de logística');
        await auditThemes(page, ui);

        await table
            .getByRole('link', { name: 'Ver los acuerdos de Reunión de logística' })
            .first()
            .click();
        await expect(page.getByText('Acuerdos de')).toBeVisible();
        await expect(page.getByRole('table', { name: 'Acuerdos' })).toContainText('ISD-1');
    });

    test('filtra acuerdos y reuniones por comité y por fechas', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/meeting/search');

        const filters = page.locator('#agreement-filters');
        if (!(await filters.evaluate((details) => details.open))) {
            await filters.locator('summary').click();
        }
        await choose(filters.getByRole('combobox', { name: 'Comité', exact: true }), 'Logística');
        await filters.getByLabel('Celebrada desde').fill('2020-01-01');
        await filters.getByRole('button', { name: 'Aplicar' }).click();

        await expect(page).toHaveURL(/desde=2020-01-01/);
        await expect(page.getByRole('table', { name: 'Acuerdos' })).toContainText('ISD-1');
        await ui.noOverflow();

        await page
            .getByRole('navigation', { name: 'Buscar en' })
            .getByRole('link', { name: 'Reuniones' })
            .click();
        await expect(page).toHaveURL(/tab=reuniones/);
        await expect(page).toHaveURL(/desde=2020-01-01/);
        await expect(page.getByRole('table', { name: 'Reuniones' })).toContainText(
            'Reunión de logística',
        );

        await choose(
            filters.getByRole('combobox', { name: 'Comité', exact: true }),
            'Comunicación',
        );
        await filters.getByRole('button', { name: 'Aplicar' }).click();
        await expect(page.getByRole('heading', { name: 'Ninguna reunión coincide' })).toBeVisible();

        await filters.getByRole('link', { name: 'Limpiar' }).click();
        await expect(page.getByRole('table', { name: 'Reuniones' })).toContainText(
            'Reunión de logística',
        );
    });

    test('una búsqueda sin resultados lo dice', async ({ page }) => {
        await signIn(page, 'estudiante');
        await page.goto('/meeting/search?q=zzzz-sin-nada');
        await expect(page.getByRole('heading', { name: 'Ningún acuerdo coincide' })).toBeVisible();
    });
});
