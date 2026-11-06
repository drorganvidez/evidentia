import { expect, test } from '../fixtures/ui.js';

const DISMISSIBLE = 'La memoria de la edición anterior';

test.describe('Alertas', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-alert');
    });

    test('el aviso descartable se cierra con su botón', async ({ ui }) => {
        const section = ui.section('alert');
        const alert = section.getByRole('note').filter({ hasText: DISMISSIBLE });

        await expect(alert).toBeVisible();

        await alert.getByRole('button', { name: 'Cerrar aviso' }).click();

        await expect(alert).toBeHidden();
        await expect(section.getByRole('note')).toHaveCount(4);
        expect(await ui.axe('#ui-alert')).toEqual([]);
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-alert')).toEqual([]);
    });
});
