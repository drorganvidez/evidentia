import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// The hour bonuses of every committee, read only, for the president and the lecturer
test.describe('Bonos de la gestión', () => {
    test('el presidente ve los bonos de todos los comités y los filtra', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/bonus/list');

        await expect(page.getByRole('heading', { level: 1, name: 'Bonos' })).toBeVisible();
        const table = page.getByRole('region', { name: 'Bonos de todos los comités' });
        await expect(table.getByText('Montaje del escenario')).toBeVisible();
        await expect(table.getByText('Pérez Romero, Lucía')).toBeVisible();
        await expect(page.getByRole('link', { name: 'Nuevo bono' })).toHaveCount(0);
        await ui.noOverflow();

        await page.getByRole('searchbox', { name: 'Filtrar bonos' }).fill('nada parecido');
        await expect(table.getByText('Montaje del escenario')).toBeHidden();
        await page.getByRole('searchbox', { name: 'Filtrar bonos' }).fill('pérez');
        await expect(table.getByText('Montaje del escenario')).toBeVisible();
    });

    for (const [who, path] of [
        ['profesor', '/lecture/bonus/list'],
        ['presidente', '/president/bonus/list'],
    ]) {
        test(`pasa axe en claro y oscuro en ${path}`, async ({ page, ui }) => {
            await signIn(page, who);
            await page.goto(path);
            await expect(page.getByRole('heading', { level: 1, name: 'Bonos' })).toBeVisible();
            expect(await ui.axe()).toEqual([]);

            await ui.setTheme('dark');
            expect(await ui.axe()).toEqual([]);
            await ui.noOverflow();
        });
    }
});
