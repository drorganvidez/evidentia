import { expect, test } from './fixtures/ui.js';

// axe on the whole catalogue takes a while with many sections
test.setTimeout(90_000);

for (const theme of ['light', 'dark']) {
    test(`el sistema de diseño cumple WCAG 2.2 AA en tema ${theme === 'dark' ? 'oscuro' : 'claro'}`, async ({
        page,
        ui,
    }) => {
        await page.goto('/_ui');
        await ui.setTheme(theme);

        await expect(page.getByRole('heading', { level: 1 })).toBeAttached();

        const sections = await page.locator('main section').count();
        expect(sections, 'Secciones del catálogo').toBeGreaterThan(0);

        const violations = await ui.axe();

        expect(violations, JSON.stringify(violations, null, 2)).toEqual([]);
        await ui.noOverflow();
    });
}
