import { expect, test } from '../fixtures/ui.js';

const BAR = '[x-data="pageProgress"]';

test.describe('Barra de carga', () => {
    test('aparece cuando la página siguiente tarda y desaparece al llegar', async ({ page }) => {
        await page.goto('/_ui/shell');

        const bar = page.locator(BAR);

        await expect(bar).toHaveAttribute('aria-hidden', 'true');
        await expect(bar).not.toHaveAttribute('data-active', 'true');

        // The next page takes a second and a half to answer
        await page.route('**/_ui', async (route) => {
            await new Promise((resolve) => setTimeout(resolve, 1500));
            await route.continue();
        });

        // The old document goes away with the navigation, so it writes down in the session that the bar lit up;
        // the link is a plain link of the app, the same on every width
        await page.evaluate(() => {
            const element = document.querySelector('[x-data="pageProgress"]');

            new MutationObserver(() => {
                if (element.getAttribute('data-active') === 'true') {
                    sessionStorage.setItem('progress-seen', 'yes');
                }
            }).observe(element, { attributes: true });

            const link = document.createElement('a');
            link.href = '/_ui';
            link.textContent = 'Ir al catálogo';
            document.getElementById('main').prepend(link);
        });
        await page.getByRole('link', { name: 'Ir al catálogo' }).click();
        await page.waitForURL(/\/_ui$/);

        expect(await page.evaluate(() => sessionStorage.getItem('progress-seen'))).toBe('yes');
        await expect(page.locator(BAR)).not.toHaveAttribute('data-active', 'true');
    });

    test('no aparece al saltar a un ancla de la misma página', async ({ page }) => {
        await page.goto('/_ui');

        await page
            .getByRole('navigation', { name: 'Contenido' })
            .getByRole('link', { name: 'Botones' })
            .click();
        await page.waitForTimeout(400);

        await expect(page.locator(BAR)).not.toHaveAttribute('data-active', 'true');
    });

    test('tampoco con un envío que otro módulo cancela', async ({ page }) => {
        await page.goto('/_ui#ui-minutes-editor');

        const section = page.locator('#ui-minutes-editor');
        const firstTitle = section.getByRole('textbox', { name: /Título/ }).first();

        await firstTitle.fill('');
        await section.getByRole('button', { name: /Guardar/ }).click();
        await page.waitForTimeout(400);

        await expect(page.locator(BAR)).not.toHaveAttribute('data-active', 'true');
    });
});
