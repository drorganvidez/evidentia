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

    test('al cerrarlo con el teclado el foco pasa al control que lo sigue', async ({
        page,
        ui,
    }) => {
        // Hidden notes included: the evaluate below reads the alert once it has closed
        const alert = ui
            .section('alert')
            .getByRole('note', { includeHidden: true })
            .filter({ hasText: DISMISSIBLE });

        await alert.getByRole('button', { name: 'Cerrar aviso' }).focus();
        await page.keyboard.press('Enter');
        await expect(alert).toBeHidden();

        const landing = await alert.evaluate((element) => {
            const active = document.activeElement;

            return {
                body: active === document.body,
                inside: element.contains(active),
                after: Boolean(
                    element.compareDocumentPosition(active) & Node.DOCUMENT_POSITION_FOLLOWING,
                ),
            };
        });

        expect(landing).toEqual({ body: false, inside: false, after: true });
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-alert')).toEqual([]);
    });
});
