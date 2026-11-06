import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// The meeting calls, signature sheets and default lists of every committee, read only, for the management
test.describe('Convocatorias, hojas de firmas y listas de la gestión', () => {
    // several pages and two audits of axe per test, slow on the emulated phone
    test.slow();

    test('el presidente recorre las secciones de las reuniones', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/meeting/list');
        const tabs = page.getByRole('navigation', { name: 'Secciones de las reuniones' });

        await tabs.getByRole('link', { name: 'Convocatorias' }).click();
        await expect(page.getByRole('heading', { level: 1, name: 'Convocatorias' })).toBeVisible();
        const requests = page.getByRole('region', { name: 'Convocatorias de todos los comités' });
        await expect(
            requests.getByRole('link', { name: 'Convocatoria de logística', exact: true }),
        ).toBeVisible();
        await expect(page.getByRole('link', { name: 'Nueva convocatoria' })).toHaveCount(0);
        await ui.noOverflow();

        await tabs.getByRole('link', { name: 'Hojas de firmas' }).click();
        await expect(
            page.getByRole('heading', { level: 1, name: 'Hojas de firmas' }),
        ).toBeVisible();
        const sheets = page.getByRole('region', { name: 'Hojas de firmas de todos los comités' });
        await expect(sheets.getByText('Pérez Romero, Lucía')).toBeVisible();
        await ui.noOverflow();

        await tabs.getByRole('link', { name: 'Listas' }).click();
        await expect(page.getByRole('heading', { level: 1, name: 'Listas' })).toBeVisible();
        const lists = page.getByRole('region', { name: 'Listas de todos los comités' });
        await expect(lists.getByText('Equipo de montaje')).toBeVisible();
        await ui.noOverflow();

        await tabs.getByRole('link', { name: 'Convocatorias' }).click();
        await requests
            .getByRole('link', { name: 'Convocatoria de logística', exact: true })
            .click();
        await expect(
            page.getByRole('heading', { level: 1, name: 'Convocatoria de logística' }),
        ).toBeVisible();
        await expect(page.getByRole('link', { name: 'Descargar PDF' })).toHaveAttribute(
            'href',
            /\/download\/request\/\d+$/,
        );
    });

    for (const path of [
        '/president/meeting/request/list',
        '/president/meeting/signaturesheet/list',
        '/lecture/meeting/defaultlist/list',
    ]) {
        test(`pasa axe en claro y oscuro en ${path}`, async ({ page, ui }) => {
            await signIn(page, path.startsWith('/lecture') ? 'profesor' : 'presidente');
            await page.goto(path);
            await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
            expect(await ui.axe()).toEqual([]);

            await ui.setTheme('dark');
            expect(await ui.axe()).toEqual([]);
            await ui.noOverflow();
        });
    }
});
