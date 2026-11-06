import { expect, test } from '../fixtures/ui.js';

// Scoped to the component: <output> elements elsewhere on the page also have the status role
const STATUS = '[x-data="notices"] [role="status"]';
const ALERT = '[x-data="notices"] [role="alert"]';
const NOTICE = '[aria-label="Avisos"] [role="group"]';

/** Focuses the last control before the stack of notices, so the next Tab enters the stack, and returns it. */
async function focusBeforeTheStack(page) {
    return page.evaluateHandle(() => {
        const stack = document.querySelector('[aria-label="Avisos"]');
        const controls = [
            ...document.querySelectorAll('a[href], button, input, select, textarea, summary'),
        ].filter(
            (control) =>
                control.tabIndex >= 0 &&
                !control.disabled &&
                control.checkVisibility() &&
                control.compareDocumentPosition(stack) & Node.DOCUMENT_POSITION_FOLLOWING,
        );
        const last = controls.at(-1);

        last.focus();

        return last;
    });
}

test.describe('Avisos', () => {
    test.beforeEach(async ({ page }) => {
        // Paused after the load (a running fake clock gets ahead of the wall clock): the 5 s and 8 s timers
        // only run when the test moves it
        const start = Date.now();

        await page.clock.install({ time: start });
        await page.goto('/_ui#ui-notices');
        await page.clock.pauseAt(start + 60_000);
    });

    // Runs the zero-delay timers left behind (Alpine throws the errors of a handler from one) before the console
    // guard of the fixture has its say
    test.afterEach(async ({ page }) => {
        await page.clock.runFor(1);
    });

    test('un aviso de éxito se muestra, se anuncia en la región de estado y se cierra a los 8 s', async ({
        page,
        ui,
    }) => {
        await ui.section('notices').getByRole('button', { name: 'Aviso de éxito' }).click();

        const notice = page.locator(NOTICE).filter({ hasText: 'Evidencia guardada' });

        await expect(notice).toBeVisible();
        await expect(notice).toHaveAttribute('data-tone', 'accepted');
        await expect(page.locator(STATUS)).toHaveText('Evidencia guardada');
        await expect(page.locator(ALERT)).toHaveText('');

        await page.clock.fastForward(7_000);
        await expect(notice).toBeVisible();

        await page.clock.fastForward(1_500);
        await expect(notice).toHaveCount(0);
    });

    test('un aviso de error se anuncia como alerta y permanece hasta que se cierra', async ({
        page,
        ui,
    }) => {
        await ui.section('notices').getByRole('button', { name: 'Aviso de error' }).click();

        const notice = page.locator(NOTICE).filter({ hasText: 'No se ha podido guardar' });

        await expect(notice).toBeVisible();
        await expect(notice).toHaveAttribute('data-tone', 'rejected');
        await expect(page.locator(ALERT)).toHaveText('No se ha podido guardar la evidencia');
        await expect(page.locator(STATUS)).toHaveText('');

        await page.clock.fastForward(30_000);
        await expect(notice).toBeVisible();

        // axe needs real timers: the clock runs again and nothing time-bound is checked from here on
        await page.clock.resume();
        expect(await ui.axe('[aria-label="Avisos"]')).toEqual([]);

        await notice.getByRole('button', { name: 'Cerrar aviso' }).click();
        await expect(notice).toHaveCount(0);
    });

    test('el puntero sobre el aviso detiene la cuenta atrás', async ({ page, ui }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'El hover es cosa del puntero fino');

        await ui.section('notices').getByRole('button', { name: 'Aviso informativo' }).click();

        const notice = page.locator(NOTICE).filter({ hasText: 'nueva versión' });

        await expect(notice).toBeVisible();
        await notice.hover();

        await page.clock.fastForward(15_000);
        await expect(notice).toBeVisible();

        await page.mouse.move(0, 0);
        await page.clock.fastForward(9_000);
        await expect(notice).toHaveCount(0);
    });

    test('el foco en el cierre de un aviso detiene la cuenta atrás mientras lo tiene', async ({
        page,
        ui,
    }) => {
        await ui.section('notices').getByRole('button', { name: 'Aviso de éxito' }).click();

        const notice = page.locator(NOTICE).filter({ hasText: 'Evidencia guardada' });
        const close = notice.getByRole('button', { name: 'Cerrar aviso' });

        await expect(notice).toBeVisible();

        // The stack follows the header in the document, so Tab reaches it from the last control of the header
        await focusBeforeTheStack(page);
        await page.keyboard.press('Tab');
        await expect(close).toBeFocused();

        await page.clock.fastForward(15_000);
        await expect(notice).toBeVisible();

        await page.keyboard.press('Tab');
        await expect(close).not.toBeFocused();
        await page.clock.fastForward(9_000);
        await expect(notice).toHaveCount(0);
    });

    test('si el puntero sale pero el foco sigue en el aviso, la cuenta atrás no vuelve', async ({
        page,
        ui,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'El hover es cosa del puntero fino');

        await ui.section('notices').getByRole('button', { name: 'Aviso informativo' }).click();

        const notice = page.locator(NOTICE).filter({ hasText: 'nueva versión' });

        await notice.hover();
        await focusBeforeTheStack(page);
        await page.keyboard.press('Tab');
        await expect(notice.getByRole('button', { name: 'Cerrar aviso' })).toBeFocused();

        await page.mouse.move(0, 0);
        await page.clock.fastForward(15_000);
        await expect(notice).toBeVisible();

        // Once the focus leaves too, the countdown runs again
        await page.keyboard.press('Tab');
        await page.clock.fastForward(9_000);
        await expect(notice).toHaveCount(0);
    });

    test('al cerrar un aviso con el teclado el foco pasa al siguiente y después vuelve de donde vino', async ({
        page,
        ui,
    }) => {
        const section = ui.section('notices');

        await section.getByRole('button', { name: 'Aviso de error' }).click();
        await section.getByRole('button', { name: 'Aviso de atención' }).click();

        const notices = page.locator(NOTICE);

        await expect(notices).toHaveCount(2);

        const origin = await focusBeforeTheStack(page);

        await page.keyboard.press('Tab');
        await expect(notices.nth(0).getByRole('button', { name: 'Cerrar aviso' })).toBeFocused();

        await page.keyboard.press('Enter');
        await expect(notices).toHaveCount(1);
        await expect(notices.nth(0)).toContainText('El plazo de subida cierra en dos horas');
        await expect(notices.nth(0).getByRole('button', { name: 'Cerrar aviso' })).toBeFocused();

        await page.keyboard.press('Enter');
        await expect(notices).toHaveCount(0);
        expect(await origin.evaluate((control) => control === document.activeElement)).toBe(true);
    });

    test('los avisos que un módulo empuja al store se anuncian por sí solos', async ({ page }) => {
        await page.evaluate(() =>
            window.Alpine.store('notices').push({
                tone: 'rejected',
                text: 'No se ha podido subir el archivo',
            }),
        );

        await expect(
            page.locator(NOTICE).filter({ hasText: 'No se ha podido subir el archivo' }),
        ).toBeVisible();
        await expect(page.locator(ALERT)).toHaveText('No se ha podido subir el archivo');
        await expect(page.locator(STATUS)).toHaveText('');

        await page.evaluate(() =>
            window.Alpine.store('notices').push({ tone: 'accepted', text: 'Archivo subido' }),
        );

        await expect(page.locator(STATUS)).toHaveText('Archivo subido');
    });

    test('el anuncio llega a la región de estado y los avisos se apilan', async ({ page, ui }) => {
        const section = ui.section('notices');

        await section.getByRole('button', { name: 'Anunciar en la región de estado' }).click();
        await expect(page.locator(STATUS)).toHaveText('Anuncio de prueba');
        await expect(page.locator(NOTICE)).toHaveCount(0, { timeout: 1000 });

        // The announcement is cleared so the same text can be announced again
        await page.clock.fastForward(5_500);
        await expect(page.locator(STATUS)).toHaveText('');

        // Pushed last: the fixed stack may cover the buttons of a short page
        await section.getByRole('button', { name: 'Aviso de atención' }).click();
        await section.getByRole('button', { name: 'Aviso de éxito' }).click();

        await expect(page.locator(NOTICE)).toHaveCount(2);
        await expect(page.locator(ALERT)).toHaveText('El plazo de subida cierra en dos horas');
        await expect(page.locator(STATUS)).toHaveText('Evidencia guardada');
        await ui.noOverflow();
    });
});
