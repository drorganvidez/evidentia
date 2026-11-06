import { expect, test } from '../fixtures/ui.js';

test.describe('Desplegables', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-disclosure');
    });

    test('el acordeón exclusivo abre uno y cierra el anterior', async ({ ui }) => {
        const section = ui.section('disclosure');
        const programme = section.locator('details[name="ui-committees"]').nth(0);
        const logistics = section.locator('details[name="ui-committees"]').nth(1);

        await expect(programme).toHaveAttribute('open', '');
        await expect(logistics).not.toHaveAttribute('open', '');
        await expect(programme.getByText('Convocatoria de la reunión 3')).toBeVisible();

        await logistics.locator('summary').click();

        await expect(logistics).toHaveAttribute('open', '');
        await expect(programme).not.toHaveAttribute('open', '');
        await expect(programme.getByText('Convocatoria de la reunión 3')).toBeHidden();
    });

    test('se abre y se cierra con el teclado y expone su estado', async ({ page, ui }) => {
        const details = ui.section('disclosure').locator('details').filter({
            hasText: 'Cómo se calculan las horas',
        });
        const summary = details.locator('summary');
        const body = details.getByText('Cada evidencia aceptada');

        await expect(details).not.toHaveAttribute('open', '');
        await expect(body).toBeHidden();

        await summary.focus();
        await page.keyboard.press('Enter');
        await expect(details).toHaveAttribute('open', '');
        await expect(body).toBeVisible();

        await page.keyboard.press('Space');
        await expect(details).not.toHaveAttribute('open', '');
        await expect(body).toBeHidden();
    });

    test('el título con nivel es un encabezado y el resumen mide al menos 44 px', async ({
        ui,
    }, testInfo) => {
        const section = ui.section('disclosure');

        await expect(
            section.getByRole('heading', { level: 4, name: 'Comité de Programa' }),
        ).toBeVisible();
        await expect(section.getByRole('heading', { name: 'Detalles técnicos' })).toHaveCount(0);

        const summaries = section.locator('summary');
        const count = await summaries.count();

        expect(count).toBeGreaterThanOrEqual(6);

        for (let i = 0; i < count; i++) {
            const box = await summaries.nth(i).boundingBox();

            expect(box).not.toBeNull();
            expect(box.height, `Resumen ${i + 1}`).toBeGreaterThanOrEqual(44);
        }

        if (testInfo.project.name === 'mobile') {
            await ui.noOverflow();
        }
    });

    test('el pequeño sin nivel abre su contenido, que no habla del componente', async ({ ui }) => {
        const small = ui
            .section('disclosure')
            .locator('details')
            .filter({ hasText: 'Detalles técnicos' });

        await small.locator('summary').click();

        await expect(small).toHaveAttribute('open', '');
        await expect(
            small.getByText('PDF de 2,4 MB subido el 12/10/2026 a las 17:40.'),
        ).toBeVisible();
        await expect(small).not.toContainText('Tamaño pequeño');
    });

    test('la apertura se anima salvo con movimiento reducido', async ({ page, ui }) => {
        const details = ui.section('disclosure').locator('details').first();
        const longest = async () =>
            Math.max(
                ...(
                    await details.evaluate(
                        (element) =>
                            getComputedStyle(element, '::details-content').transitionDuration,
                    )
                )
                    .split(',')
                    .map((duration) => parseFloat(duration)),
            );

        expect(await longest(), 'Con movimiento').toBeGreaterThan(0);

        await page.emulateMedia({ reducedMotion: 'reduce' });
        expect(await longest(), 'Con movimiento reducido').toBeLessThanOrEqual(0.001);
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-disclosure')).toEqual([]);
    });
});
