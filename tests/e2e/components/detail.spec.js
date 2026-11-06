import { expect, test } from '../fixtures/ui.js';

/** Waits for the finite animations (entrances); the aurora's infinite ones are left alone. */
async function settle(page) {
    await page.evaluate(() =>
        Promise.race([
            Promise.all(
                document
                    .getAnimations()
                    .filter((animation) => animation.effect.getTiming().iterations !== Infinity)
                    .map((animation) => animation.finished),
            ),
            new Promise((resolve) => setTimeout(resolve, 3000)),
        ]),
    );
}

test.describe('Detalle de una evidencia', () => {
    test('compone la pantalla completa con el shell y sin errores', async ({ page, ui }) => {
        const response = await page.goto('/_ui/detail');

        expect(response.status()).toBe(200);

        await expect(
            page.getByRole('heading', { level: 1, name: 'Montaje del photocall' }),
        ).toBeVisible();
        await expect(page.getByRole('main')).toHaveAttribute('aria-labelledby', 'page-title');
        await expect(page.getByRole('list', { name: 'Recorrido de la evidencia' })).toBeVisible();
        await expect(
            page.getByRole('note').filter({ hasText: 'Tu coordinador necesita más pruebas' }),
        ).toBeVisible();
        await expect(
            page.getByRole('list', { name: 'Pruebas de la evidencia' }).getByRole('listitem'),
        ).toHaveCount(3);
        await expect(page.getByRole('list', { name: 'Historial de la evidencia' })).toBeAttached();
        await expect(page.getByText('EV-0033').first()).toBeVisible();
        await expect(page.locator('[x-data="upload"]')).toBeAttached();
        await ui.noOverflow();
    });

    test('la eliminación pide confirmación en un diálogo de alerta', async ({ page }) => {
        await page.goto('/_ui/detail');

        const trigger = page.getByRole('button', { name: 'Eliminar', exact: true });
        await trigger.click();

        const dialog = page.getByRole('alertdialog', { name: 'Eliminar la evidencia' });
        await expect(dialog).toBeVisible();
        await expect(dialog.getByRole('button', { name: 'Cancelar' })).toBeFocused();
        await expect(dialog.getByRole('button', { name: 'Sí, eliminar' })).toBeVisible();

        await page.keyboard.press('Escape');
        await expect(dialog).toBeHidden();
        await expect(trigger).toBeFocused();
    });

    test('en el teléfono apila el lateral bajo el contenido y muestra la barra inferior', async ({
        page,
        ui,
        isMobile,
    }) => {
        test.skip(!isMobile, 'Solo en el proyecto móvil');

        await page.goto('/_ui/detail');

        await expect(
            page.getByRole('navigation', { name: 'Navegación principal (móvil)' }),
        ).toBeVisible();
        await expect(page.getByRole('button', { name: 'Abrir menú' })).toBeVisible();

        const proofs = await page
            .getByRole('heading', { name: 'Pruebas', exact: true })
            .boundingBox();
        const data = await page.getByRole('heading', { name: 'Datos', exact: true }).boundingBox();

        expect(data.y, 'El lateral va debajo de las pruebas').toBeGreaterThan(proofs.y);
        await ui.noOverflow();
    });

    for (const theme of ['light', 'dark']) {
        test(`cumple WCAG 2.2 AA en tema ${theme === 'dark' ? 'oscuro' : 'claro'}`, async ({
            page,
            ui,
        }) => {
            await page.goto('/_ui/detail');
            await ui.setTheme(theme);
            await expect(
                page.getByRole('heading', { level: 1, name: 'Montaje del photocall' }),
            ).toBeVisible();
            await settle(page);

            const violations = await ui.axe();

            expect(violations, JSON.stringify(violations, null, 2)).toEqual([]);
        });
    }
});
