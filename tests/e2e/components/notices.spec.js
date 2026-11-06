import { expect, test } from '../fixtures/ui.js';

// Scoped to the component: <output> elements elsewhere on the page also have the status role
const STATUS = '[x-data="notices"] [role="status"]';
const ALERT = '[x-data="notices"] [role="alert"]';
const NOTICE = '[aria-label="Avisos"] [role="group"]';

test.describe('Avisos', () => {
    test.beforeEach(async ({ page }) => {
        // Paused after the load (a running fake clock gets ahead of the wall clock): the 5 s and 8 s timers
        // only run when the test moves it
        const start = Date.now();

        await page.clock.install({ time: start });
        await page.goto('/_ui#ui-notices');
        await page.clock.pauseAt(start + 60_000);
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
