import { signIn } from '../../fixtures/auth.js';
import { choose, expect, test } from '../../fixtures/ui.js';

async function audit(page, ui) {
    await ui.noOverflow();

    for (const theme of ['light', 'dark']) {
        await ui.setTheme(theme);
        expect(await ui.axe(), `axe en ${theme}`).toEqual([]);
    }
}

// An evidence done by two students: the author names the partner, who reads it without touching it
test.describe('Evidencias en coautoría', () => {
    test('la autora nombra a un compañero y él la ve en sus evidencias', async ({
        page,
        ui,
    }, testInfo) => {
        // two people, two pages and five audits
        test.slow();
        const title = `Montaje del escenario ${testInfo.project.name} ${Date.now()}`;

        await signIn(page, 'estudiante');
        await page.goto('/evidence/create');
        await expect(page.getByRole('combobox', { name: 'Coautores' })).toBeVisible();
        await audit(page, ui);
        await page.getByLabel('Título').fill(title);
        await page.getByLabel('Horas').fill('2');
        await choose(page.getByRole('combobox', { name: 'Comité', exact: true }), 'Logística');
        const coauthors = page.getByRole('combobox', { name: 'Coautores' });
        await coauthors.fill('garcía');
        await page.getByRole('option', { name: /García López, Juan/ }).click();
        await expect(
            page.getByRole('list', { name: 'Seleccionados' }).getByRole('listitem'),
        ).toHaveCount(1);
        expect(await ui.axe(), 'axe con un coautor elegido').toEqual([]);
        await page.getByLabel('Descripción').fill('Montaje y desmontaje del escenario principal.');
        await page.getByRole('button', { name: 'Publicar', exact: true }).click();
        await page.getByRole('alertdialog').getByRole('button', { name: 'Sí, publicar' }).click();
        await expect(page.getByText('Evidencia creada con éxito.').first()).toBeVisible();

        await page.context().clearCookies();
        await signIn(page, 'companero');
        await page.goto('/evidence/list');
        const table = page.getByRole('table', { name: 'Evidencias en coautoría' });
        await expect(table).toBeVisible();
        await audit(page, ui);
        await table.getByRole('link', { name: title, exact: true }).click();

        await expect(page.getByRole('heading', { level: 1 })).toHaveText(title);
        await expect(page.getByRole('term').filter({ hasText: 'Coautores' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Editar', exact: true })).toHaveCount(0);
        await audit(page, ui);
    });
});
