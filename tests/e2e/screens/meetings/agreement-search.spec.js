import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
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

    test('una búsqueda sin resultados lo dice', async ({ page }) => {
        await signIn(page, 'estudiante');
        await page.goto('/meeting/search?q=zzzz-sin-nada');
        await expect(page.getByRole('heading', { name: 'Ningún acuerdo coincide' })).toBeVisible();
    });
});
