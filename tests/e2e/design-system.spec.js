import { expect, test } from '@playwright/test';

test.describe('Página del sistema de diseño', () => {
    test('se abre bajo la CSP estricta sin errores en consola', async ({ page }) => {
        const errors = [];
        page.on('console', (message) => {
            if (message.type() === 'error') {
                errors.push(message.text());
            }
        });
        page.on('pageerror', (error) => errors.push(error.message));

        const response = await page.goto('/_ui');

        expect(response.status()).toBe(200);

        const csp = response.headers()['content-security-policy'];
        expect(csp).toContain("script-src 'self' 'nonce-");
        expect(csp).not.toContain('unsafe-inline');
        expect(csp).not.toContain('unsafe-eval');

        await expect(page).toHaveTitle(/Sistema de diseño/);
        await expect(page.getByRole('heading', { name: 'Color' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Botones' })).toBeVisible();

        // A blocked script or style would show up here as "Refused to ..."
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
});
