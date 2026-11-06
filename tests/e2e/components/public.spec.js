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

/**
 * Records the text of the first figure on every frame from the moment the document is parsed (Alpine has
 * started by then), so the trail does not depend on how long the page takes to load.
 */
async function recordFirstFigure(page) {
    await page.addInitScript(() => {
        window.figureTrail = [];
        document.addEventListener('DOMContentLoaded', () => {
            const figure = document.querySelector('[x-data="countUp"]');
            const read = () => {
                window.figureTrail.push(figure.textContent.trim());

                if (window.figureTrail.length < 30) {
                    requestAnimationFrame(read);
                }
            };

            read();
        });
    });
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

    test('la entrada ligera trae el contador que x-ui.field pone a un textarea con límite', async ({
        page,
    }) => {
        await page.goto('/_ui/public');

        // The markup the field prints for type="textarea" with maxlength; Alpine picks it up when it lands
        await page.evaluate(() => {
            const field = document.createElement('div');

            field.setAttribute('x-data', 'charCount');
            field.innerHTML =
                '<label for="field-notes">Observaciones</label>' +
                '<textarea id="field-notes" name="notes" maxlength="20" x-on:input="update"></textarea>' +
                '<span id="field-notes-count" x-text="text">0 de 20 caracteres</span>';
            document.getElementById('sign-form').append(field);
        });

        await page.locator('#field-notes').fill('Llegué tarde');
        await expect(page.locator('#field-notes-count')).toHaveText('12 de 20 caracteres');
    });

    test('el ojo muestra y oculta la contraseña', async ({ page }) => {
        await page.goto('/_ui/public');

        const password = page.locator('#field-password');
        const toggle = page.getByRole('button', { name: 'Mostrar la contraseña' });

        await expect(password).toHaveAttribute('type', 'password');
        await expect(toggle).toHaveAttribute('aria-pressed', 'false');

        // A toggle button keeps its name and says its state with aria-pressed
        await toggle.click();
        await expect(password).toHaveAttribute('type', 'text');
        await expect(toggle).toHaveAttribute('aria-pressed', 'true');

        await toggle.click();
        await expect(password).toHaveAttribute('type', 'password');
        await expect(toggle).toHaveAttribute('aria-pressed', 'false');
    });

    for (const [form, fields] of Object.entries({
        Entrar: { '#field-user': 'juan.perez', '#field-password': 'una contraseña' },
        Firmar: { '#field-uvus': 'juaper', '#field-name': 'Juan Pérez García' },
    })) {
        test(`el formulario de «${form}» se envía y la página vuelve con el aviso`, async ({
            page,
        }) => {
            await page.goto('/_ui/public');

            for (const [selector, value] of Object.entries(fields)) {
                await page.locator(selector).fill(value);
            }

            await page.getByRole('button', { name: form }).click();

            await expect(page).toHaveURL(/\/_ui\/public$/);
            await expect(
                page
                    .locator('[aria-label="Avisos"] [role="group"]')
                    .filter({ hasText: 'Formulario recibido' }),
            ).toBeVisible();
            await expect(page.locator('[x-data="notices"] [role="status"]')).toHaveText(
                'Formulario recibido',
            );
        });
    }

    test('las cifras cuentan desde cero y llegan a su valor en menos de 2 s', async ({ page }) => {
        await recordFirstFigure(page);
        await page.goto('/_ui/public');

        const figures = page.locator('[x-data="countUp"]');

        await expect(figures).toHaveCount(3);
        await expect(figures.nth(0)).toHaveText('1.204', { timeout: 2000 });
        await expect(figures.nth(1)).toHaveText('312', { timeout: 2000 });
        await expect(figures.nth(2)).toHaveText('10,5', { timeout: 2000 });
        expect((await page.evaluate(() => window.figureTrail))[0], 'Primera cifra').toBe('0');
    });

    test('con movimiento reducido las cifras muestran su valor final desde el principio', async ({
        page,
    }) => {
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await recordFirstFigure(page);
        await page.goto('/_ui/public');

        await expect.poll(() => page.evaluate(() => window.figureTrail.length)).toBe(30);
        expect(new Set(await page.evaluate(() => window.figureTrail))).toEqual(new Set(['1.204']));
        await expect(page.locator('[x-data="countUp"]').nth(2)).toHaveText('10,5');
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
