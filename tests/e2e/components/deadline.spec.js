import { expect, test } from '../fixtures/ui.js';

const HOUR = 3_600_000;
const DAY = 24 * HOUR;

test.describe('Plazos', () => {
    test.beforeEach(async ({ page }) => {
        // The demo dates are relative to the server clock; the page clock starts at the same moment
        await page.clock.install({ time: Date.now() });
        await page.goto('/_ui#ui-deadline');
    });

    test('el servidor pinta el texto y el tono de cada estado', async ({ ui }) => {
        const section = ui.section('deadline');

        await expect(section.locator('[data-demo="open"] [data-tone]')).toHaveText('14 días');
        await expect(section.locator('[data-demo="open"] [data-tone]')).toHaveAttribute(
            'data-tone',
            'pending',
        );
        await expect(section.locator('[data-demo="urgent"] [data-tone]')).toHaveText('2 días');
        await expect(section.locator('[data-demo="urgent"] [data-tone]')).toHaveAttribute(
            'data-tone',
            'rejected',
        );
        await expect(section.locator('[data-demo="closed"] [data-tone]')).toHaveText(
            'Plazo cerrado',
        );
        await expect(section.locator('[data-demo="closed"] [data-tone]')).toHaveAttribute(
            'data-tone',
            'draft',
        );
        await expect(section.locator('[data-demo="none"] [data-tone]')).toHaveText('Sin plazo');
        await expect(section.locator('[data-demo="linked"] a[x-data="deadline"]')).toHaveAttribute(
            'href',
            /#ui-deadline$/,
        );
    });

    test('al avanzar el reloj el plazo pasa a urgente y a cerrado', async ({ page, ui }) => {
        const section = ui.section('deadline');
        const urgent = section.locator('[data-demo="urgent"] [data-tone]');
        const open = section.locator('[data-demo="open"] [data-tone]');

        await expect(urgent).toHaveText('2 días');

        // Two days and seven hours later the urgent one has closed and the open one is closer
        await page.clock.fastForward(2 * DAY + 7 * HOUR);

        await expect(urgent).toHaveText('Plazo cerrado');
        await expect(urgent).toHaveAttribute('data-tone', 'draft');
        await expect(section.locator('[data-demo="urgent"] [x-text="closesVerb"]')).toHaveText(
            'Cerró el',
        );
        await expect(open).toHaveText('12 días');
        await expect(open).toHaveAttribute('data-tone', 'pending');

        // Nine more days: under the three warning days the open one turns urgent
        await page.clock.fastForward(9 * DAY + 12 * HOUR);

        await expect(open).toHaveText('2 días');
        await expect(open).toHaveAttribute('data-tone', 'rejected');
    });

    test('la sección cumple WCAG 2.2 AA y no desborda', async ({ ui }) => {
        expect(await ui.axe('#ui-deadline')).toEqual([]);
        await ui.noOverflow();
    });
});
