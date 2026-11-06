import { expect, test } from '../fixtures/ui.js';

// The Clipboard API only exists in secure contexts. CI reaches the app on 127.0.0.1, which is one;
// the Docker runs reach it through a plain host name, which Chromium is told to treat as secure.
// The headless shell ignores that flag, so this file runs on the full Chromium build.
const origin = new URL(process.env.APP_URL ?? 'http://127.0.0.1:8000').origin;

test.use({
    channel: 'chromium',
    launchOptions: { args: [`--unsafely-treat-insecure-origin-as-secure=${origin}`] },
});

test.describe('Botones', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-button');
    });

    test('copia el texto, lo anuncia y vuelve al icono inicial', async ({
        page,
        context,
        ui,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'El portapapeles se comprueba en chromium');

        await context.grantPermissions(['clipboard-read', 'clipboard-write']);

        const button = ui.section('button').getByRole('button', { name: 'Copiar enlace' });
        const copyIcon = button.locator('svg[x-show="!copied"]');
        const checkIcon = button.locator('svg[x-show="copied"]');

        await expect(copyIcon).toBeVisible();
        await expect(checkIcon).toBeHidden();

        await button.click();

        await expect(checkIcon).toBeVisible();
        await expect(copyIcon).toBeHidden();
        await expect(
            page.getByRole('status').filter({ hasText: 'Copiado al portapapeles' }),
        ).toHaveCount(1);
        expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(
            'https://evidentia.us.es/sign/4821',
        );

        // Two seconds later the button shows the copy icon again
        await expect(copyIcon).toBeVisible({ timeout: 5000 });
        await expect(checkIcon).toBeHidden();
    });

    test('los botones de icono copian su propio texto', async ({ page, context, ui }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'El portapapeles se comprueba en chromium');

        await context.grantPermissions(['clipboard-read', 'clipboard-write']);

        await ui.section('button').getByRole('button', { name: 'Copiar el identificador' }).click();

        expect(await page.evaluate(() => navigator.clipboard.readText())).toBe('EV-0033');
    });

    test('todos los objetivos miden al menos 44 px en móvil', async ({ page, ui }, testInfo) => {
        test.skip(
            testInfo.project.name !== 'mobile',
            'El objetivo de 44 px se exige con puntero grueso',
        );

        expect(await page.evaluate(() => matchMedia('(pointer: coarse)').matches)).toBe(true);

        const section = ui.section('button');
        const targets = section.locator('a, button');
        const count = await targets.count();

        expect(count).toBeGreaterThan(20);

        for (let i = 0; i < count; i++) {
            const target = targets.nth(i);
            const box = await target.boundingBox();
            const name = await target.evaluate(
                (el) => el.getAttribute('aria-label') ?? el.textContent.trim(),
            );

            expect(box, name).not.toBeNull();
            expect(box.height, `Altura de «${name}»`).toBeGreaterThanOrEqual(44);
            expect(box.width, `Anchura de «${name}»`).toBeGreaterThanOrEqual(44);
        }

        await ui.noOverflow();
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-button')).toEqual([]);
    });
});
