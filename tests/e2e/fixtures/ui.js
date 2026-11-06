import AxeBuilder from '@axe-core/playwright';
import { test as base, expect } from '@playwright/test';

/**
 * Shared fixtures of the UI tests.
 *
 * - `page` fails the test when the browser logs an error, a page error or a CSP message.
 * - `ui.axe()` audits the page (or a selector) against WCAG 2.2 AA and returns the violations.
 * - `ui.setTheme('dark')` stores the choice and reloads; `ui.noOverflow()` checks the reflow.
 */
export const test = base.extend({
    page: async ({ page }, use) => {
        const errors = [];

        page.on('console', (message) => {
            if (message.type() === 'error' || message.text().includes('Content Security Policy')) {
                errors.push(message.text());
            }
        });
        page.on('pageerror', (error) => errors.push(error.message));

        await use(page);

        expect(errors, 'Errores en la consola del navegador').toEqual([]);
    },

    ui: async ({ page }, use) => {
        await use({
            async axe(selector = null, { disable = [] } = {}) {
                let builder = new AxeBuilder({ page }).withTags([
                    'wcag2a',
                    'wcag2aa',
                    'wcag21a',
                    'wcag21aa',
                    'wcag22aa',
                ]);

                if (selector) {
                    builder = builder.include(selector);
                }

                if (disable.length) {
                    builder = builder.disableRules(disable);
                }

                const { violations } = await builder.analyze();

                return violations.map((violation) => ({
                    id: violation.id,
                    impact: violation.impact,
                    help: violation.help,
                    nodes: violation.nodes.slice(0, 5).map((node) => node.target.join(' ')),
                }));
            },

            async setTheme(mode) {
                await page.evaluate((value) => {
                    if (value === 'system') {
                        localStorage.removeItem('evidentia.theme');
                    } else {
                        localStorage.setItem('evidentia.theme', value);
                    }
                }, mode);
                await page.reload();
            },

            section(id) {
                return page.locator(`#ui-${id}`);
            },

            async noOverflow() {
                const widths = await page.evaluate(() => ({
                    scroll: document.documentElement.scrollWidth,
                    client: document.documentElement.clientWidth,
                }));

                expect(widths.scroll, 'Desborde horizontal').toBeLessThanOrEqual(widths.client);
            },
        });
    },
});

export { expect };
