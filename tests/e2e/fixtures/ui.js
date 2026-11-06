import AxeBuilder from '@axe-core/playwright';
import { test as base, expect } from '@playwright/test';

/**
 * Warnings that are errors in disguise. Alpine reports a broken expression with console.warn and only throws
 * it later from a timer, which never runs under a paused fake clock; its other warnings never throw at all.
 */
const ALPINE_WARNINGS = ['Alpine Expression Error', 'Alpine Warning', 'CSP Parser Error'];

/**
 * Runs in the page (self-contained, Playwright serialises it): the colours a person sees for an element, as sRGB
 * triplets. `behind` composites the backgrounds of its ancestors over the white canvas of the browser, `fill` adds
 * the element's own background, and `text`, `border` and each of `extra` (any other colour property, such as the
 * stops of a gradient) fade by the opacity of the element and its ancestors and land on `fill`, which also reaches
 * under the border. A 1 px canvas turns any CSS colour (oklch, color-mix) into sRGB.
 */
function paintInPage(element, properties) {
    const canvas = document.createElement('canvas');
    canvas.width = 1;
    canvas.height = 1;
    const context = canvas.getContext('2d', { willReadFrequently: true });

    const rgba = (value) => {
        context.clearRect(0, 0, 1, 1);
        // An unparsable value leaves the transparent reset in place
        context.fillStyle = 'rgb(0 0 0 / 0)';
        context.fillStyle = value;
        context.fillRect(0, 0, 1, 1);
        const [r, g, b, a] = context.getImageData(0, 0, 1, 1).data;

        return { r, g, b, a: a / 255 };
    };
    const over = (top, bottom) => {
        const alpha = top.a + bottom.a * (1 - top.a);
        const channel = (key) =>
            alpha === 0 ? 0 : (top[key] * top.a + bottom[key] * bottom.a * (1 - top.a)) / alpha;

        return { r: channel('r'), g: channel('g'), b: channel('b'), a: alpha };
    };
    const triplet = ({ r, g, b }) => [r, g, b];

    const ancestors = [];
    let opacity = 1;

    for (let node = element.parentElement; node; node = node.parentElement) {
        ancestors.unshift(node);
    }

    for (let node = element; node; node = node.parentElement) {
        opacity *= Number(getComputedStyle(node).opacity);
    }

    const behind = ancestors.reduce(
        (below, node) => over(rgba(getComputedStyle(node).backgroundColor), below),
        { r: 255, g: 255, b: 255, a: 1 },
    );
    const style = getComputedStyle(element);
    const fill = over(rgba(style.backgroundColor), behind);
    const faded = (value) => {
        const color = rgba(value);

        return triplet(over({ ...color, a: color.a * opacity }, fill));
    };

    return {
        behind: triplet(behind),
        fill: triplet(fill),
        text: faded(style.color),
        border: faded(style.borderTopColor),
        extra: Object.fromEntries(
            properties.map((name) => [name, faded(style.getPropertyValue(name))]),
        ),
    };
}

/** WCAG 2 contrast ratio of two sRGB triplets (0 to 255 per channel). */
export function contrast(first, second) {
    const luminance = (rgb) => {
        const [r, g, b] = rgb.map((value) => {
            const channel = value / 255;

            return channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4;
        });

        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    };
    const [lighter, darker] = [luminance(first), luminance(second)].sort((a, b) => b - a);

    return (lighter + 0.05) / (darker + 0.05);
}

/**
 * Chooses an option of a select field (<x-ui.field type="select">) the way a person does: a click opens the
 * combobox and a click on the option of its listbox chooses it. The option is found by its whole name.
 */
export async function choose(combobox, option) {
    await combobox.click();

    const list = combobox.page().locator(`[id="${await combobox.getAttribute('aria-controls')}"]`);

    await list.getByRole('option', { name: option, exact: true }).click();
    await expect(combobox).toHaveAttribute('aria-expanded', 'false');
    await expect(combobox).toHaveValue(option);
}

/**
 * Shared fixtures of the UI tests.
 *
 * - `page` fails the test when the browser logs an error, a page error, a CSP message or an Alpine warning.
 * - `ui.axe()` audits the page (or a selector) against WCAG 2.2 AA and returns the violations.
 * - `ui.setTheme('dark')` stores the choice and reloads; `ui.noOverflow()` checks the reflow.
 * - `ui.paint(locator)` returns the colours of an element as seen on screen, for `contrast()`; axe does not
 *   measure icons nor text painted with a gradient.
 */
export const test = base.extend({
    page: async ({ page }, use) => {
        const errors = [];

        page.on('console', (message) => {
            const text = message.text();

            if (
                message.type() === 'error' ||
                text.includes('Content Security Policy') ||
                (message.type() === 'warning' &&
                    ALPINE_WARNINGS.some((prefix) => text.startsWith(prefix)))
            ) {
                errors.push(text);
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

            paint(locator, properties = []) {
                return locator.evaluate(paintInPage, properties);
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
