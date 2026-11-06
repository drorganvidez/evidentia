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

        // How many there are is the business of DesignSystemPageTest, which compares them with the files
        expect(count, 'Enlaces de la tabla de contenidos').toBeGreaterThan(0);

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

    test('en modo Sistema el tema sigue la preferencia del sistema', async ({ page, isMobile }) => {
        const scheme = () =>
            page.evaluate(() => getComputedStyle(document.documentElement).colorScheme);

        await page.emulateMedia({ colorScheme: 'dark' });
        await page.goto('/_ui');

        const html = page.locator('html');
        const region = await themeRegion(page, isMobile);

        // From an explicit choice, so that going back to the system has something to undo
        await region.locator('label', { has: page.getByRole('radio', { name: 'Claro' }) }).click();
        await expect(html).toHaveAttribute('data-theme', 'light');
        expect(await scheme()).toBe('light');

        await region
            .locator('label', { has: page.getByRole('radio', { name: 'Sistema' }) })
            .click();
        await expect(region.getByRole('radio', { name: 'Sistema' })).toBeChecked();
        await expect(html).not.toHaveAttribute('data-theme');
        expect(await scheme()).toBe('dark');

        await page.emulateMedia({ colorScheme: 'light' });
        await expect.poll(scheme).toBe('light');

        await page.reload();
        await expect(html).not.toHaveAttribute('data-theme');
        expect(await scheme()).toBe('light');
    });

    test('el anillo de foco no cambia la forma de lo que enfoca', async ({ page }) => {
        await page.goto('/_ui');

        await page.keyboard.press('Tab');
        await expect(page.getByRole('link', { name: 'Saltar al contenido' })).toBeFocused();
        await page.keyboard.press('Enter');

        const main = page.locator('#main');

        await expect(main).toBeFocused();
        expect(
            await main.evaluate((element) => ({
                ring: element.matches(':focus-visible'),
                radius: getComputedStyle(element).borderRadius,
            })),
        ).toEqual({ ring: true, radius: '0px' });
    });

    test('el margen inferior del desplazamiento reserva la barra inferior solo donde la hay', async ({
        page,
    }) => {
        await page.goto('/_ui');

        const bar = page.getByRole('navigation', { name: 'Navegación principal (móvil)' });
        const padding = await page.evaluate(() =>
            parseFloat(getComputedStyle(document.documentElement).scrollPaddingBottom),
        );

        if (await bar.isVisible()) {
            // An element scrolled into view stops above the bar (WCAG 2.4.11)
            const { y } = await bar.boundingBox();

            expect(padding).toBeGreaterThanOrEqual(page.viewportSize().height - y);
        } else {
            expect(padding).toBeLessThanOrEqual(16);
        }
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
