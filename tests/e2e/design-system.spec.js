import { expect, test } from '@playwright/test';

/** Collects console errors, page errors and any CSP message, whatever its type or wording. */
function watchConsole(page) {
    const errors = [];

    page.on('console', (message) => {
        if (message.type() === 'error' || message.text().includes('Content Security Policy')) {
            errors.push(message.text());
        }
    });
    page.on('pageerror', (error) => errors.push(error.message));

    return errors;
}

test.describe('Página del sistema de diseño', () => {
    test('se abre bajo la CSP estricta sin errores en consola', async ({ page }) => {
        const errors = watchConsole(page);

        const response = await page.goto('/_ui');

        expect(response.status()).toBe(200);

        const csp = response.headers()['content-security-policy'];
        expect(csp).toContain("script-src 'self' 'nonce-");
        expect(csp).not.toContain('unsafe-inline');
        expect(csp).not.toContain('unsafe-eval');

        await expect(page).toHaveTitle(/Sistema de diseño/);
        await expect(
            page.getByRole('heading', { level: 1, name: 'Sistema de diseño' }),
        ).toBeAttached();
        await expect(page.getByRole('heading', { name: 'Color' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Botones' })).toBeVisible();

        expect(errors).toEqual([]);
    });

    test('el tema se cambia y se recuerda', async ({ page }) => {
        await page.goto('/_ui');

        const html = page.locator('html');
        const dark = page.getByRole('button', { name: 'Oscuro' });

        await dark.click();
        await expect(html).toHaveAttribute('data-theme', 'dark');
        await expect(dark).toHaveAttribute('aria-pressed', 'true');

        await page.reload();
        await expect(html).toHaveAttribute('data-theme', 'dark');

        await page.getByRole('button', { name: 'Claro' }).click();
        await expect(html).toHaveAttribute('data-theme', 'light');
    });

    test('no desborda a 320 px de ancho', async ({ page }) => {
        await page.setViewportSize({ width: 320, height: 568 });
        await page.goto('/_ui');

        const widths = await page.evaluate(() => ({
            scroll: document.documentElement.scrollWidth,
            client: document.documentElement.clientWidth,
        }));

        expect(widths.scroll).toBeLessThanOrEqual(widths.client);
        await expect(page.getByRole('button', { name: 'Oscuro' })).toBeVisible();
    });
});
