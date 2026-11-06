import { contrast, expect, test } from './fixtures/ui.js';

/**
 * Contrast that axe cannot measure: icons (SVG) and the colours of controls under translucent layers. Every
 * check reads the computed colours in the browser and composites them over the stack of backgrounds.
 */
const THEMES = { light: 'claro', dark: 'oscuro' };

test.describe('Contraste de lo que axe no mide', () => {
    for (const [theme, name] of Object.entries(THEMES)) {
        test(`los botones atenuados superan 3 a 1 en reposo en tema ${name}`, async ({
            page,
            ui,
        }) => {
            await page.goto('/_ui#ui-button');
            await ui.setTheme(theme);

            // The «Abrir» icons of the table rows and the group of dim buttons of the button section
            const dim = page
                .locator('#ui-table td a[aria-label^="Abrir "], #ui-button div.group button')
                .filter({ visible: true });
            const count = await dim.count();

            expect(count, 'Botones atenuados visibles').toBeGreaterThan(2);

            for (let i = 0; i < count; i++) {
                const button = dim.nth(i);
                const label = await button.getAttribute('aria-label');
                const paint = await ui.paint(button);

                expect(
                    contrast(paint.text, paint.fill),
                    `Icono de «${label}»`,
                ).toBeGreaterThanOrEqual(3);
            }
        });
    }

    for (const [name, { theme, colorScheme }] of Object.entries({
        claro: { theme: 'light' },
        oscuro: { theme: 'dark' },
        'oscuro del sistema': { theme: 'system', colorScheme: 'dark' },
    })) {
        test(`con más contraste el borde de un campo supera 3 a 1 en tema ${name}`, async ({
            page,
            ui,
        }) => {
            await page.emulateMedia({ contrast: 'more', colorScheme });
            await page.goto('/_ui/public');
            await ui.setTheme(theme);

            // The box around the input carries the border, between the field and the card
            const paint = await ui.paint(page.locator('#field-user').locator('xpath=..'));

            expect(
                contrast(paint.border, paint.fill),
                'Borde frente al campo',
            ).toBeGreaterThanOrEqual(3);
            expect(
                contrast(paint.border, paint.behind),
                'Borde frente a la tarjeta',
            ).toBeGreaterThanOrEqual(3);
        });
    }
});
