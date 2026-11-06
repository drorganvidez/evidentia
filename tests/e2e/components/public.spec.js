import { contrast, expect, test } from '../fixtures/ui.js';

/** Waits for the finite animations (entrances, count-ups); the aurora's infinite ones are left alone. */
async function settle(page) {
    await page.evaluate(() =>
        Promise.race([
            Promise.all(
                document
                    .getAnimations()
                    .filter((animation) => animation.effect.getTiming().iterations !== Infinity)
                    .map((animation) => animation.finished),
            ),
            new Promise((resolve) => setTimeout(resolve, 3000)),
        ]),
    );
}

test.describe('Página de acceso', () => {
    test('se abre con la entrada ligera, bajo la CSP y sin errores', async ({ page, ui }) => {
        const response = await page.goto('/_ui/public');

        expect(response.status()).toBe(200);
        expect(response.headers()['content-security-policy']).toContain(
            "script-src 'self' 'nonce-",
        );

        await expect(page.getByRole('heading', { level: 1 })).toContainText(
            'Lo que haces en las jornadas',
        );
        await expect(page.getByRole('button', { name: 'Entrar' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Saltar al formulario' })).toBeAttached();

        const scripts = await page
            .locator('script[type="module"][src*="/build/assets/"]')
            .evaluateAll((nodes) => nodes.map((node) => node.getAttribute('src')));

        expect(
            scripts.some((src) => /\/public-[^/]+\.js$/.test(src)),
            'Entrada public.js',
        ).toBe(true);
        expect(
            scripts.some((src) => /\/app-[^/]+\.js$/.test(src)),
            'Sin app.js',
        ).toBe(false);

        await ui.noOverflow();
    });

    test('el ojo muestra y oculta la contraseña', async ({ page }) => {
        await page.goto('/_ui/public');

        const password = page.locator('#field-password');
        const toggle = page.getByRole('button', { name: 'Mostrar la contraseña' });

        await expect(password).toHaveAttribute('type', 'password');
        await expect(toggle).toHaveAttribute('aria-pressed', 'false');

        await toggle.click();
        await expect(password).toHaveAttribute('type', 'text');

        const hide = page.getByRole('button', { name: 'Ocultar la contraseña' });
        await expect(hide).toHaveAttribute('aria-pressed', 'true');

        await hide.click();
        await expect(password).toHaveAttribute('type', 'password');
    });

    test('las cifras llegan a su valor', async ({ page }) => {
        await page.goto('/_ui/public');

        const figures = page.locator('[x-data="countUp"]');

        await expect(figures).toHaveCount(3);
        await expect(figures.nth(0)).toHaveText('1.204');
        await expect(figures.nth(1)).toHaveText('312');
        await expect(figures.nth(2)).toHaveText('10,5');
    });

    test('en el teléfono es una sola columna sin desbordar', async ({ page, ui, isMobile }) => {
        test.skip(!isMobile, 'Solo en el proyecto móvil');

        await page.goto('/_ui/public');

        const claim = await page.getByRole('heading', { level: 1 }).boundingBox();
        const card = await page.getByRole('heading', { name: 'Iniciar sesión' }).boundingBox();

        expect(card.y, 'La tarjeta va debajo del titular').toBeGreaterThan(claim.y + claim.height);
        await ui.noOverflow();
    });

    for (const theme of ['light', 'dark']) {
        // axe leaves text painted with a gradient as incomplete: each stop is measured against the canvas
        test(`los tonos del degradado del titular superan 3 a 1 sobre el lienzo en tema ${theme === 'dark' ? 'oscuro' : 'claro'}`, async ({
            page,
            ui,
        }) => {
            await page.goto('/_ui/public');
            await ui.setTheme(theme);

            const stops = ['--tw-gradient-from', '--tw-gradient-via', '--tw-gradient-to'];
            const paint = await ui.paint(
                page.getByRole('heading', { level: 1 }).locator('em'),
                stops,
            );

            for (const stop of stops) {
                expect(contrast(paint.extra[stop], paint.behind), stop).toBeGreaterThanOrEqual(3);
            }
        });

        test(`cumple WCAG 2.2 AA en tema ${theme === 'dark' ? 'oscuro' : 'claro'}`, async ({
            page,
            ui,
        }) => {
            await page.goto('/_ui/public');
            await ui.setTheme(theme);
            await expect(page.locator('[x-data="countUp"]').nth(2)).toHaveText('10,5');
            await settle(page);

            const violations = await ui.axe();

            expect(violations, JSON.stringify(violations, null, 2)).toEqual([]);
        });
    }
});
