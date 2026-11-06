import { contrast, expect, test } from './fixtures/ui.js';

/** The media features of the system that change the design: print and forced colours. */
const PAPER = [255, 255, 255];

test.describe('Medios del sistema', () => {
    test('al imprimir con el tema oscuro el texto sale oscuro y el cristal, blanco y plano', async ({
        page,
        ui,
    }) => {
        await page.goto('/_ui/detail');
        await ui.setTheme('dark');
        await page.emulateMedia({ media: 'print' });

        expect(
            await page.evaluate(() => getComputedStyle(document.documentElement).colorScheme),
        ).toBe('light');

        const card = page.locator('main .glass').first();
        const surface = await card.evaluate((element) => {
            const style = getComputedStyle(element);

            return { shadow: style.boxShadow, filter: style.backdropFilter };
        });

        expect(surface).toEqual({ shadow: 'none', filter: 'none' });
        expect((await ui.paint(card)).fill).toEqual(PAPER);

        // The title, a status text on its tinted surface and the muted line of the authorship
        const texts = {
            Título: page.getByRole('heading', { level: 1 }),
            Motivo: page.getByText('Tu coordinador necesita más pruebas'),
            Rol: page.getByText('Coordinador de Logística'),
        };

        for (const [name, locator] of Object.entries(texts)) {
            const paint = await ui.paint(locator);

            expect(contrast(paint.text, PAPER), `${name} sobre el papel`).toBeGreaterThanOrEqual(
                4.5,
            );
            expect(
                contrast(paint.text, paint.fill),
                `${name} sobre su fondo`,
            ).toBeGreaterThanOrEqual(4.5);
        }
    });

    for (const path of ['/_ui/detail', '/_ui/public']) {
        test(`con colores forzados ${path} cumple WCAG 2.2 AA y no desborda`, async ({
            page,
            ui,
        }) => {
            await page.emulateMedia({ forcedColors: 'active' });
            await page.goto(path);

            expect(await page.evaluate(() => matchMedia('(forced-colors: active)').matches)).toBe(
                true,
            );

            // The entrances fade in: axe measures once the finite animations are over
            await page.evaluate(() =>
                Promise.all(
                    document
                        .getAnimations()
                        .filter((animation) => animation.effect.getTiming().iterations !== Infinity)
                        .map((animation) => animation.finished),
                ),
            );

            const violations = await ui.axe();

            expect(violations, JSON.stringify(violations, null, 2)).toEqual([]);
            await ui.noOverflow();
        });
    }
});
