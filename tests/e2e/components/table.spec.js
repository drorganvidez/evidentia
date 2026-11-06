import { expect, test } from '../fixtures/ui.js';

// The demo rows link to the catalogue itself with an evidence id; the destination is stubbed so the
// navigation is observed without reloading the whole page.
const destination = /\/_ui\?evidence=\d+/;

async function stubDestination(page) {
    const hits = [];

    await page.route(destination, (route) => {
        hits.push(route.request().url());

        return route.fulfill({
            status: 200,
            contentType: 'text/html; charset=utf-8',
            body: '<!doctype html><html lang="es"><head><title>Destino</title></head><body><main><h1>Destino</h1></main></body></html>',
        });
    });

    return hits;
}

/** Records every navigation the page starts: the Navigation API fires navigate synchronously. */
async function trackNavigations(page) {
    await page.evaluate(() => {
        window.uiNavigations = [];
        navigation.addEventListener('navigate', (event) =>
            window.uiNavigations.push(event.destination.url),
        );
    });

    return () => page.evaluate(() => window.uiNavigations);
}

test.describe('Tablas', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-table');
    });

    test('el clic en una celda de la fila navega a su destino', async ({ page, ui }) => {
        const hits = await stubDestination(page);
        const row = ui
            .section('table')
            .getByRole('row')
            .filter({ hasText: 'Montaje del photocall' });

        await row.getByRole('cell').filter({ hasText: '1,75 h' }).click();

        await page.waitForURL(destination);
        expect(hits).toHaveLength(1);
        expect(page.url()).toContain('evidence=33');
    });

    test('el clic central en una fila abre su destino en otra pestaña', async ({
        page,
        context,
        ui,
    }) => {
        const opened = [];

        await context.route(destination, (route) => {
            opened.push(route.request().url());

            return route.fulfill({
                status: 200,
                contentType: 'text/html; charset=utf-8',
                body: '<!doctype html><html lang="es"><head><title>Destino</title></head><body><main><h1>Destino</h1></main></body></html>',
            });
        });

        const navigations = await trackNavigations(page);
        const row = ui
            .section('table')
            .getByRole('row')
            .filter({ hasText: 'Montaje del photocall' });
        const tab = context.waitForEvent('page', { timeout: 10_000 });

        await row.getByRole('cell').filter({ hasText: '1,75 h' }).click({ button: 'middle' });

        const opener = await tab;

        await opener.waitForLoadState();
        expect(opener.url()).toContain('evidence=33');
        expect(opened).toHaveLength(1);
        expect(await navigations(), 'La pestaña actual no navega').toEqual([]);
        await expect(page).toHaveURL(/\/_ui#ui-table$/);
        await opener.close();
    });

    test('el enlace del título navega una sola vez', async ({ page, ui }) => {
        const hits = await stubDestination(page);

        await ui
            .section('table')
            .getByRole('link', { name: 'Montaje del photocall', exact: true })
            .click();

        await page.waitForURL(destination);
        await page.waitForLoadState('load');
        expect(hits).toHaveLength(1);
    });

    test('seleccionar texto en la fila no navega', async ({ page, ui }) => {
        const hits = await stubDestination(page);
        const navigations = await trackNavigations(page);
        const row = ui
            .section('table')
            .getByRole('row')
            .filter({ hasText: 'Montaje del photocall' });
        const text = row.getByText('Instalación del fondo y de las luces');
        const box = await text.boundingBox();
        const y = box.y + box.height / 2;

        // Counts the clicks that reach the row after its own handler, which decides synchronously
        await row.evaluate((el) => {
            window.uiRowClicks = 0;
            el.addEventListener('click', () => {
                window.uiRowClicks += 1;
            });
        });

        await page.mouse.move(box.x + 2, y);
        await page.mouse.down();
        await page.mouse.move(box.x + Math.min(box.width - 2, 120), y, { steps: 8 });
        await page.mouse.up();

        expect(await page.evaluate(() => getSelection().toString().length)).toBeGreaterThan(0);
        expect(await page.evaluate(() => window.uiRowClicks), 'El clic llega a la fila').toBe(1);
        expect(await navigations(), 'La fila no inicia ninguna navegación').toEqual([]);
        expect(hits).toHaveLength(0);
        await expect(page).toHaveURL(/\/_ui#ui-table$/);
    });

    test('el filtro reduce las filas y anuncia el recuento', async ({ ui }) => {
        const table = ui.section('table').locator('section[aria-label="Mis evidencias"]');
        const rows = table.locator('tbody tr');
        const visibleRows = rows.filter({ visible: true });
        const status = table.getByRole('status');
        const filter = table.getByRole('searchbox', { name: 'Filtrar evidencias' });

        await expect(rows).toHaveCount(6);
        await expect(status).toHaveText('6 evidencias');

        await filter.fill('photocall');
        await expect(visibleRows).toHaveCount(1);
        await expect(status).toHaveText('1 de 6 evidencias');

        // Accents and case are ignored
        await filter.fill('REUNION');
        await expect(visibleRows).toHaveCount(1);
        await expect(visibleRows.first()).toContainText('Acta de la reunión 3');

        await filter.fill('aceptada');
        await expect(visibleRows).toHaveCount(3);
        await expect(status).toHaveText('3 de 6 evidencias');

        await filter.fill('');
        await expect(visibleRows).toHaveCount(6);
        await expect(status).toHaveText('6 evidencias');
    });

    test('mientras el filtro espera, el recuento sigue diciendo lo que muestran las filas', async ({
        page,
        ui,
    }) => {
        // The page clock is paused after the load, so the 150 ms debounce only runs when the test moves it
        const start = Date.now();

        await page.clock.install({ time: start });
        await page.goto('/_ui#ui-table');
        await page.clock.pauseAt(start + 60_000);

        const table = ui.section('table').locator('section[aria-label="Mis evidencias"]');
        const visibleRows = table.locator('tbody tr').filter({ visible: true });
        const status = table.getByRole('status');
        const filter = table.getByRole('searchbox', { name: 'Filtrar evidencias' });

        await filter.fill('photocall');
        await expect(visibleRows).toHaveCount(6);
        await expect(status).toHaveText('6 evidencias');

        await page.clock.runFor(200);
        await expect(visibleRows).toHaveCount(1);
        await expect(status).toHaveText('1 de 6 evidencias');

        await filter.fill('');
        await expect(visibleRows).toHaveCount(1);
        await expect(status).toHaveText('1 de 6 evidencias');

        await page.clock.runFor(200);
        await expect(visibleRows).toHaveCount(6);
        await expect(status).toHaveText('6 evidencias');
        await page.clock.resume();
    });

    test('la región desplazable solo es enfocable cuando desborda', async ({
        page,
        ui,
    }, testInfo) => {
        test.skip(
            testInfo.project.name !== 'chromium',
            'El tramo de tableta se comprueba en escritorio',
        );

        await page.setViewportSize({ width: 768, height: 1024 });

        const regions = ui.section('table').locator('[x-data="scrollRegion"]');
        const count = await regions.count();
        let overflowing = 0;

        expect(count).toBeGreaterThan(0);

        for (let i = 0; i < count; i++) {
            const region = regions.nth(i);
            const overflows = await region.evaluate((el) => el.scrollWidth > el.clientWidth);

            if (overflows) {
                overflowing += 1;
                await expect(region).toHaveAttribute('tabindex', '0');
                await expect(region).toHaveAttribute('role', 'group');
                await expect(region).toHaveAttribute('aria-label', 'Tabla desplazable');
            } else {
                await expect.poll(() => region.getAttribute('tabindex')).toBeNull();
                await expect.poll(() => region.getAttribute('role')).toBeNull();
            }
        }

        expect(overflowing, 'La tabla de gestión desborda a 768 px').toBeGreaterThan(0);
    });

    test('en móvil las filas son tarjetas y nada desborda', async ({ ui }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'Las tarjetas se comprueban en móvil');

        const table = ui.section('table').locator('section[aria-label="Mis evidencias"]');
        const row = table.locator('tbody tr').first();

        expect(await row.evaluate((el) => getComputedStyle(el).display)).toBe('grid');
        await expect(table.locator('thead')).toBeHidden();
        await expect(
            table.getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toBeVisible();
        await expect(table.getByRole('link', { name: 'Abrir Montaje del photocall' })).toBeHidden();
        await ui.noOverflow();
    });

    test('en las tarjetas cada celda nombra su columna a los lectores de pantalla', async ({
        ui,
        isMobile,
    }) => {
        const table = ui.section('table').locator('section[aria-label="Mis evidencias"]');

        // The thead is hidden on the phone; from md the column header names the cell instead
        await expect(
            table.getByRole('cell', { name: isMobile ? 'Horas: 1,75 h' : '1,75 h', exact: true }),
        ).toBeVisible();

        const label = table.getByText('Horas:', { exact: true }).first();

        if (isMobile) {
            // Read by screen readers but never painted
            expect((await label.boundingBox()).width).toBeLessThanOrEqual(1);
        } else {
            await expect(label).toBeHidden();
        }
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-table')).toEqual([]);
    });
});
