import { expect, test } from './fixtures/ui.js';

/** The theme switch lives in the topbar on wide screens and inside the drawer on the phone. */
async function themeRegion(page, isMobile) {
    if (!isMobile) {
        return page.getByRole('banner');
    }

    await page.getByRole('button', { name: 'Abrir menú' }).click();

    return page.getByRole('dialog', { name: 'Menú' });
}

test.describe('Página del sistema de diseño', () => {
    test('se abre bajo la CSP estricta con el shell de la aplicación', async ({ page, ui }) => {
        const response = await page.goto('/_ui');

        expect(response.status()).toBe(200);

        const csp = response.headers()['content-security-policy'];
        expect(csp).toContain("script-src 'self' 'nonce-");
        expect(csp).not.toContain('unsafe-inline');
        expect(csp).not.toContain('unsafe-eval');

        await expect(page).toHaveTitle(/Sistema de diseño/);
        await expect(
            page.getByRole('heading', { level: 1, name: 'Sistema de diseño' }),
        ).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Color' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Iconos', exact: true })).toBeVisible();
        await expect(page.getByRole('navigation', { name: 'Contenido' })).toBeVisible();
        await expect(page.getByRole('contentinfo')).toContainText('Evidentia');
        await expect(page.getByRole('link', { name: 'Saltar al contenido' })).toBeAttached();
        await ui.noOverflow();
    });

    test('la tabla de contenidos enlaza cada sección con su título', async ({ page }) => {
        await page.goto('/_ui');

        const links = page.getByRole('navigation', { name: 'Contenido' }).getByRole('link');
        const count = await links.count();

        expect(count).toBeGreaterThanOrEqual(49);

        for (let index = 0; index < count; index++) {
            const href = await links.nth(index).getAttribute('href');
            const label = (await links.nth(index).textContent()).trim();
            const section = page.locator(href);

            await expect(section, `Sección ${href}`).toBeAttached();
            await expect(section.getByRole('heading', { level: 2 }).first()).toHaveText(label);
        }
    });

    test('el tema se cambia y se recuerda', async ({ page, isMobile }) => {
        await page.goto('/_ui');

        const html = page.locator('html');
        let region = await themeRegion(page, isMobile);
        const dark = region.getByRole('radio', { name: 'Oscuro' });

        await region.locator('label', { has: page.getByRole('radio', { name: 'Oscuro' }) }).click();
        await expect(html).toHaveAttribute('data-theme', 'dark');
        await expect(dark).toBeChecked();

        await page.reload();
        await expect(html).toHaveAttribute('data-theme', 'dark');

        region = await themeRegion(page, isMobile);
        const light = region.getByRole('radio', { name: 'Claro' });

        await region.locator('label', { has: page.getByRole('radio', { name: 'Claro' }) }).click();
        await expect(html).toHaveAttribute('data-theme', 'light');
        await expect(light).toBeChecked();
    });

    test('no desborda a 320 px de ancho', async ({ page, ui }) => {
        await page.setViewportSize({ width: 320, height: 568 });
        await page.goto('/_ui');

        await expect(
            page.getByRole('heading', { level: 1, name: 'Sistema de diseño' }),
        ).toBeVisible();
        await expect(page.getByRole('button', { name: 'Abrir menú' })).toBeVisible();
        await ui.noOverflow();
    });
});
