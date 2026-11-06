import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

async function audit(page, ui, selector = null) {
    await ui.noOverflow();

    for (const theme of ['light', 'dark']) {
        await ui.setTheme(theme);
        expect(await ui.axe(selector), `axe en ${theme}`).toEqual([]);
    }
}

test.describe('Actualizaciones', () => {
    test('enseña las versiones o el aviso de GitHub', async ({ page, ui }) => {
        // Every load asks the API of GitHub from the server, three times with the themes
        test.slow();
        await signIn(page, 'estudiante');
        await page.goto('/updates');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Actualizaciones');
        await audit(page, ui);
    });
});
