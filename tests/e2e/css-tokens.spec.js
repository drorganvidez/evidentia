import { expect, test } from './fixtures/ui.js';

/**
 * Classes the stylesheet must contain: the ones Tailwind always generates (@source inline) and the
 * utilities of the design system that the catalogue exercises.
 */
const ALWAYS = [
    ...Array.from({ length: 101 }, (_, n) => `.w-${n}\\/100`),
    ...[0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330].map((hue) => `.hue-${hue}`),
    '.orb-glow',
    '.committee-dot',
    '.glass',
    '.glass-2',
    '.halo-aurora',
    '.text-control',
    '.inset-shadow-hi',
    '.shadow-glow-sm',
];

test('la hoja de estilos servida contiene las utilidades generadas', async ({ page, request }) => {
    await page.goto('/_ui');

    const href = await page
        .locator('link[rel="stylesheet"][href*="/build/assets/app-"]')
        .getAttribute('href');
    expect(href, 'Hoja de estilos compilada').toBeTruthy();

    const css = await (await request.get(href)).text();

    const missing = ALWAYS.filter((selector) => !css.includes(selector));
    expect(missing).toEqual([]);

    // The tokens of the design system, not Tailwind's stock palette
    expect(css).toContain('--color-aurora-600');
    expect(css).not.toContain('--color-blue-500');
});
