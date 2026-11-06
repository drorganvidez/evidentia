import { expect, test } from '../fixtures/ui.js';

test.describe('Diálogos', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-dialog');
    });

    test('se abre con el foco dentro, Escape lo cierra y devuelve el foco al disparador', async ({
        page,
        ui,
    }) => {
        const trigger = page.locator('button[aria-controls="demo-reject"]');
        const dialog = page.locator('#demo-reject');

        await expect(trigger).toHaveText('Rechazar');
        await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await trigger.click();

        await expect(dialog).toHaveAttribute('open', '');
        await expect(trigger).toHaveAttribute('aria-expanded', 'true');
        expect(
            await page.evaluate(() => document.activeElement.closest('#demo-reject') !== null),
        ).toBe(true);
        await expect(dialog.getByRole('heading', { name: 'Rechazar la evidencia' })).toBeVisible();
        expect(await ui.axe('#demo-reject')).toEqual([]);
        // The backdrop dims and blurs the page behind (the blur class exists in the built CSS)
        expect(
            await dialog.evaluate((el) => getComputedStyle(el, '::backdrop').backdropFilter),
        ).toContain('blur(');

        await page.keyboard.press('Escape');

        await expect(dialog).not.toHaveAttribute('open', '');
        await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await expect(trigger).toBeFocused();
    });

    test('el botón de cerrar y el clic en el fondo lo cierran', async ({ page }) => {
        const trigger = page.locator('button[aria-controls="demo-reject"]');
        const dialog = page.locator('#demo-reject');

        await trigger.click();
        await dialog.getByRole('button', { name: 'Cerrar' }).click();
        await expect(dialog).not.toHaveAttribute('open', '');

        await trigger.click();
        await expect(dialog).toHaveAttribute('open', '');
        // The top left corner is always backdrop: the sheet sits at the bottom and the modal in the middle
        await page.mouse.click(4, 4);
        await expect(dialog).not.toHaveAttribute('open', '');
    });

    test('la alerta abre con el foco en Cancelar para que Intro no destruya nada', async ({
        page,
        ui,
    }) => {
        await page.locator('button[aria-controls="demo-remove"]').click();

        const dialog = page.locator('#demo-remove');

        await expect(dialog).toHaveAttribute('role', 'alertdialog');
        await expect(dialog.getByRole('button', { name: 'Cancelar' })).toBeFocused();
        await expect(dialog.getByRole('button', { name: 'Cerrar' })).toHaveCount(0);
        expect(await ui.axe('#demo-remove')).toEqual([]);

        await page.keyboard.press('Enter');
        await expect(dialog).not.toHaveAttribute('open', '');
        await expect(page).toHaveURL(/#ui-dialog$/);
    });

    test('la alerta no se cierra al pulsar el fondo, pero sí con Escape', async ({ page }) => {
        const trigger = page.locator('button[aria-controls="demo-remove"]');
        const dialog = page.locator('#demo-remove');

        await trigger.click();
        await expect(dialog).toHaveAttribute('open', '');

        // The top left corner is always backdrop: the sheet sits at the bottom and the modal in the middle
        await page.mouse.click(4, 4);
        await expect(dialog).toHaveAttribute('open', '');
        await expect(trigger).toHaveAttribute('aria-expanded', 'true');

        await page.keyboard.press('Escape');
        await expect(dialog).not.toHaveAttribute('open', '');
        await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await expect(trigger).toBeFocused();
    });

    test('«Sí, eliminar» envía un POST con su token y vuelve con el aviso', async ({ page }) => {
        await page.locator('button[aria-controls="demo-remove"]').click();

        const sent = page.waitForRequest((request) => request.url().endsWith('/_ui/demo/submit'));

        await page.locator('#demo-remove').getByRole('button', { name: 'Sí, eliminar' }).click();

        const request = await sent;
        const body = new URLSearchParams(request.postData() ?? '');

        expect(request.method()).toBe('POST');
        expect(body.get('_id')).toBe('33');
        expect(body.get('_token')).toBeTruthy();
        await expect(
            page.getByRole('region', { name: 'Avisos' }).getByText('Formulario recibido'),
        ).toBeVisible();
    });

    test('el rechazo envía su motivo por POST y vuelve con el aviso', async ({ page }) => {
        await page.locator('button[aria-controls="demo-reject"]').click();
        await page.locator('#demo-reject-reason').fill('Falta la foto del montaje');

        const sent = page.waitForRequest((request) => request.url().endsWith('/_ui/demo/submit'));

        await page.locator('#demo-reject').getByRole('button', { name: 'Rechazar' }).click();

        const request = await sent;
        const body = new URLSearchParams(request.postData() ?? '');

        expect(request.method()).toBe('POST');
        expect(body.get('reason')).toBe('Falta la foto del montaje');
        expect(body.get('_token')).toBeTruthy();
        await expect(
            page.getByRole('region', { name: 'Avisos' }).getByText('Formulario recibido'),
        ).toBeVisible();
    });

    test('el cajón se desliza desde la izquierda y ocupa toda la altura', async ({ page, ui }) => {
        await page.locator('button[aria-controls="demo-drawer"]').click();

        const dialog = page.locator('#demo-drawer');
        const viewport = page.viewportSize();

        await expect(dialog).toHaveAttribute('open', '');
        await expect(dialog.getByRole('link', { name: 'Mis evidencias' })).toBeVisible();

        // The entry slide lasts 180 ms: wait until the drawer rests against the left edge
        await expect.poll(async () => (await dialog.boundingBox()).x).toBe(0);

        const box = await dialog.boundingBox();

        expect(box.y).toBe(0);
        expect(Math.round(box.height)).toBe(viewport.height);
        expect(box.width).toBeLessThanOrEqual(viewport.width * 0.85);
        expect(await ui.axe('#demo-drawer')).toEqual([]);

        await page.keyboard.press('Escape');
        await expect(dialog).not.toHaveAttribute('open', '');
    });

    test('en móvil el modal es una hoja inferior que toca el borde y no desborda', async ({
        page,
        ui,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'La hoja inferior es el modo del teléfono');

        await page.locator('button[aria-controls="demo-long"]').click();

        const dialog = page.locator('#demo-long');
        const viewport = page.viewportSize();

        // The sheet rises 8 px while it enters: wait until it rests on the bottom edge
        await expect
            .poll(async () => {
                const box = await dialog.boundingBox();

                return Math.round(box.y + box.height);
            })
            .toBe(viewport.height);

        const box = await dialog.boundingBox();

        expect(Math.round(box.width)).toBe(viewport.width);
        expect(box.height).toBeLessThanOrEqual(viewport.height * 0.9 + 1);
        await ui.noOverflow();

        // The long body scrolls inside the sheet and the footer stays reachable
        await dialog.getByRole('button', { name: 'Entendido' }).scrollIntoViewIfNeeded();
        await dialog.getByRole('button', { name: 'Entendido' }).click();
        await expect(dialog).not.toHaveAttribute('open', '');
    });

    test('en escritorio el modal largo cabe en la ventana y se desplaza por dentro', async ({
        page,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Comprobación del escritorio');

        await page.locator('button[aria-controls="demo-long"]').click();

        const dialog = page.locator('#demo-long');
        const viewport = page.viewportSize();

        await expect(dialog).toHaveAttribute('open', '');

        const box = await dialog.boundingBox();

        expect(box.height).toBeLessThanOrEqual(viewport.height);
        expect(
            await dialog.evaluate((el) => el.scrollHeight > el.clientHeight),
            'El cuerpo largo se desplaza dentro del diálogo',
        ).toBe(true);
    });
});
