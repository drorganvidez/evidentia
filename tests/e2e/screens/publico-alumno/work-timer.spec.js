import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

/** «00:01:05» → 65 */
function seconds(clock) {
    const [h, m, s] = clock.trim().split(':').map(Number);

    return h * 3600 + m * 60 + s;
}

const trigger = (page) => page.getByRole('button', { name: /^Cronómetro de trabajo/ });
const panel = (page) => page.getByRole('dialog', { name: 'Cronómetro de trabajo' });

/** Opens the panel of the stopwatch in the topbar (it closes with every page load). */
async function open(page) {
    await trigger(page).click();
    await expect(panel(page)).toBeVisible();
}

const clock = async (page) => seconds(await panel(page).locator('#work-timer-display').innerText());

/** The field of the evidence form: its label is exactly «Título», the one of the stopwatch is another. */
const field = (page, label) => page.getByLabel(label, { exact: true });

test.describe('Cronómetro de trabajo de la barra superior', () => {
    // Only on data of its own (a unique title) so the order of the tests and a second run never matter
    test('cuenta entre páginas y recargas, se detiene y crea la evidencia con título y duración', async ({
        page,
        ui,
    }) => {
        test.slow();
        const title = `Montaje del cartel ${Date.now()}`;

        await signIn(page, 'estudiante');
        await page.goto('/evidence/list');

        // Without a title it does not start
        await open(page);
        await panel(page).getByRole('button', { name: 'Iniciar' }).click();
        await expect(panel(page).getByRole('alert')).toContainText('Escribe un título');
        await expect(panel(page).getByText('Listo', { exact: true })).toBeVisible();

        await panel(page).getByLabel('Nombre de la sesión').fill(title);
        await panel(page).getByRole('button', { name: 'Iniciar' }).click();
        await expect(panel(page).getByText('En curso', { exact: true })).toBeVisible();
        await expect(panel(page).getByRole('button', { name: 'Pausar' })).toBeVisible();
        await expect(panel(page).getByRole('button', { name: 'Detener' })).toBeVisible();
        await expect.poll(() => clock(page)).toBeGreaterThanOrEqual(1);
        await expect(trigger(page)).toHaveAccessibleName(/en curso: 00:00:\d\d/);

        // The person has a timer on the open panel, in light and in dark
        await ui.noOverflow();
        for (const theme of ['light', 'dark']) {
            await ui.setTheme(theme);
            expect(await ui.axe(), `axe con el cronómetro abierto en ${theme}`).toEqual([]);
        }

        // Another page: it keeps counting, it did not start again
        await open(page);
        const before = await clock(page);

        await page.goto('/meeting/list');
        await expect(trigger(page)).toHaveAccessibleName(/en curso/);
        await open(page);
        await expect(panel(page).getByLabel('Nombre de la sesión')).toHaveValue(title);
        const afterNavigation = await clock(page);

        expect(afterNavigation).toBeGreaterThanOrEqual(before);

        // A reload of the browser: still counting from where it was
        await page.reload();
        await open(page);
        const afterReload = await clock(page);

        expect(afterReload).toBeGreaterThanOrEqual(afterNavigation);
        await expect.poll(() => clock(page)).toBeGreaterThan(afterReload);

        // Pausing freezes it, also after a reload
        await panel(page).getByRole('button', { name: 'Pausar' }).click();
        await expect(panel(page).getByText('En pausa', { exact: true })).toBeVisible();
        const paused = await clock(page);

        await page.waitForTimeout(2200);
        expect(await clock(page)).toBe(paused);
        await page.reload();
        await open(page);
        await expect(panel(page).getByText('En pausa', { exact: true })).toBeVisible();
        expect(await clock(page)).toBe(paused);

        // Resuming adds to what it had; stopping offers to create the evidence
        await panel(page).getByRole('button', { name: 'Reanudar' }).click();
        await expect(panel(page).getByText('En curso', { exact: true })).toBeVisible();
        await expect.poll(() => clock(page)).toBeGreaterThan(paused);
        await panel(page).getByRole('button', { name: 'Detener' }).click();

        await expect(panel(page).getByText('Listo', { exact: true })).toBeVisible();
        await expect(panel(page).getByText(title).first()).toBeVisible();
        await panel(page).getByRole('link', { name: 'Crear evidencia' }).click();

        // The form opens with the title and the duration already in it (under a minute counts as one)
        await expect(page).toHaveURL(/\/evidence\/create\?sessions(%5B|\[)\d*(%5D|\])=\d+$/);
        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Nueva evidencia');
        await expect(field(page, 'Título')).toHaveValue(title);
        await expect(field(page, 'Horas')).toHaveValue('0');
        await expect(field(page, 'Minutos')).toHaveValue('1');
    });

    test('junta varias sesiones recientes en una misma evidencia y cada persona solo ve las suyas', async ({
        page,
        browser,
    }) => {
        test.slow();
        const stamp = Date.now();
        const first = `Primera parte ${stamp}`;
        const second = `Segunda parte ${stamp}`;
        const third = `Tercera parte ${stamp}`;

        await signIn(page, 'estudiante');
        await page.goto('/evidence/list');

        for (const title of [first, second, third]) {
            await open(page);
            await panel(page).getByLabel('Nombre de la sesión').fill(title);
            await panel(page).getByRole('button', { name: 'Iniciar' }).click();
            await expect(panel(page).getByText('En curso', { exact: true })).toBeVisible();
            await expect.poll(() => clock(page)).toBeGreaterThanOrEqual(1);
            await panel(page).getByRole('button', { name: 'Detener' }).click();
            await expect(panel(page).getByText('Listo', { exact: true })).toBeVisible();
            await page.reload();
        }

        // After a reload they are all in the list of recent sessions; two of the three are chosen
        await open(page);
        const recent = panel(page).getByRole('list', { name: 'Sesiones recientes' });

        for (const title of [first, second, third]) {
            await expect(recent.getByText(title, { exact: true })).toBeVisible();
        }
        await expect(
            panel(page).getByRole('link', { name: /Crear evidencia con seleccionadas/ }),
        ).toBeHidden();

        await recent.getByRole('checkbox', { name: `Seleccionar sesión ${first}` }).check();
        await recent.getByRole('checkbox', { name: `Seleccionar sesión ${third}` }).check();
        await expect(
            panel(page).getByRole('link', {
                name: /Crear evidencia con seleccionadas \(0 h 1 min\)/,
            }),
        ).toBeVisible();
        await panel(page)
            .getByRole('link', { name: /Crear evidencia con seleccionadas/ })
            .click();

        // One evidence with the two titles together and the sum of their durations
        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Nueva evidencia');
        await expect(field(page, 'Título')).toHaveValue(new RegExp(`${first}.*${third}`));
        await expect(field(page, 'Título')).not.toHaveValue(new RegExp(second));
        await expect(field(page, 'Horas')).toHaveValue('0');
        await expect(field(page, 'Minutos')).toHaveValue('1');

        // Somebody else never sees them, neither in the topbar nor by asking for their ids
        const other = await browser.newContext();
        const otherPage = await other.newPage();

        await signIn(otherPage, 'companero');
        await otherPage.goto('/evidence/list');
        await trigger(otherPage).click();
        await expect(panel(otherPage)).toBeVisible();
        await expect(panel(otherPage)).not.toContainText(String(stamp));

        const state = await otherPage.request.get('/work-sessions', {
            headers: { Accept: 'application/json' },
        });

        expect(JSON.stringify(await state.json())).not.toContain(String(stamp));
        await other.close();

        // Removing one from the list takes it out for good
        await panel(page)
            .waitFor({ state: 'detached' })
            .catch(() => {});
        await page.goto('/evidence/list');
        await open(page);
        await panel(page)
            .getByRole('button', { name: `Eliminar sesión ${second}` })
            .click();
        await expect(panel(page).getByText(second, { exact: true })).toHaveCount(0);
        await page.reload();
        await open(page);
        await expect(panel(page).getByText(second, { exact: true })).toHaveCount(0);
        await expect(panel(page).getByText(first, { exact: true })).toBeVisible();
    });

    test('el panel cabe en la pantalla', async ({ page }) => {
        await signIn(page, 'estudiante');
        await page.goto('/evidence/list');
        await open(page);

        const box = await panel(page).boundingBox();
        const viewport = page.viewportSize();

        expect(box.x).toBeGreaterThanOrEqual(0);
        expect(box.x + box.width).toBeLessThanOrEqual(viewport.width);
    });
});
