import { expect, test } from '../fixtures/ui.js';

const pdf = (name, kilobytes = 8) => ({
    name,
    mimeType: 'application/pdf',
    buffer: Buffer.alloc(kilobytes * 1024, 'a'),
});

/**
 * The browser logs as an error every response of 400 or more, also the one a test provokes on purpose.
 * This takes over the console guard of the fixture and lets only that message through.
 */
function expectingStatus(page, status) {
    const unexpected = [];
    const expected = `Failed to load resource: the server responded with a status of ${status}`;

    page.removeAllListeners('console');
    page.on('console', (message) => {
        const text = message.text();
        const counts = message.type() === 'error' || text.includes('Content Security Policy');

        if (counts && !text.startsWith(expected)) {
            unexpected.push(text);
        }
    });

    return unexpected;
}

test.describe('Subida de archivos', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-upload');
    });

    test('muestra los archivos iniciales y sube dos nuevos con progreso hasta rellenar los ocultos', async ({
        page,
        ui,
    }) => {
        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        const rows = zone.getByRole('listitem');
        const posts = [];

        page.on('request', (request) => {
            if (request.method() === 'POST' && request.url().includes('/_ui/demo/upload')) {
                posts.push(request);
            }
        });

        await expect(rows).toHaveCount(2);
        await expect(rows.first()).toContainText('acta-reunion-3.pdf');
        await expect(rows.first()).toContainText('1,2 MB');
        await expect(rows.first()).toContainText('Ya en el servidor');
        await expect(zone.getByRole('status')).toHaveText('2 archivos, 1,2 MB');

        await zone
            .locator('input[type="file"]')
            .setInputFiles([pdf('informe.pdf'), pdf('foto.pdf', 16)]);

        await expect(rows).toHaveCount(4);
        await expect(rows.nth(2)).toContainText('informe.pdf');
        await expect(rows.nth(2)).toContainText('8 kB');
        await expect(rows.nth(2)).toContainText('Subido', { timeout: 10_000 });
        await expect(rows.nth(3)).toContainText('Subido', { timeout: 10_000 });

        const hidden = zone.locator('input[type="hidden"][name="uploaded[]"]');

        await expect(hidden).toHaveCount(2);
        expect(await hidden.first().inputValue()).toMatch(/^demo-/);
        expect(posts).toHaveLength(2);
        expect(posts[0].headers()['x-csrf-token']).toBeTruthy();
        expect(posts[0].headers()['x-requested-with']).toBe('XMLHttpRequest');
        await expect(zone.getByRole('status')).toHaveText('4 archivos, 1,2 MB');

        // The file input is emptied so the form does not post the files a second time
        expect(
            await zone.locator('input[type="file"]').evaluate((input) => input.files.length),
        ).toBe(0);
    });

    test('un archivo que pesa demasiado se rechaza con un aviso y sin petición', async ({
        page,
        ui,
    }) => {
        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        let posts = 0;

        await page.route('**/_ui/demo/upload', async (route) => {
            posts += 1;
            await route.continue();
        });

        await zone.locator('input[type="file"]').setInputFiles([pdf('enorme.pdf', 1500)]);

        await expect(zone.getByRole('alert')).toHaveText('enorme.pdf: el archivo pesa más de 1 MB');
        await expect(zone.getByRole('listitem')).toHaveCount(2);
        expect(posts).toBe(0);
        // Said once, next to the zone: no notice repeats it
        await expect(
            page.getByRole('region', { name: 'Avisos' }).filter({ hasText: 'enorme.pdf' }),
        ).toHaveCount(0);

        await zone.locator('input[type="file"]').setInputFiles([
            {
                name: 'script.exe',
                mimeType: 'application/octet-stream',
                buffer: Buffer.alloc(10),
            },
        ]);

        await expect(zone.getByRole('alert')).toHaveText('script.exe: tipo de archivo no admitido');
    });

    test('quitar un archivo subido avisa al servidor y retira el oculto', async ({ page, ui }) => {
        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        const rows = zone.getByRole('listitem');

        await zone.locator('input[type="file"]').setInputFiles([pdf('informe.pdf')]);
        await expect(rows.nth(2)).toContainText('Subido', { timeout: 10_000 });
        await expect(zone.locator('input[type="hidden"][name="uploaded[]"]')).toHaveCount(1);

        const revert = page.waitForRequest(
            (request) =>
                request.method() === 'DELETE' && request.url().includes('/_ui/demo/upload'),
        );

        await zone.getByRole('button', { name: 'Quitar informe.pdf' }).click();

        expect((await revert).postData()).toMatch(/^demo-/);
        await expect(rows).toHaveCount(2);
        await expect(zone.locator('input[type="hidden"][name="uploaded[]"]')).toHaveCount(0);
        await expect(zone.locator('input[type="file"]')).toBeFocused();

        const remove = page.waitForRequest((request) =>
            request.url().includes('/_ui/demo/remove/foto-photocall.jpg'),
        );

        await zone.getByRole('button', { name: 'Quitar foto-photocall.jpg' }).click();

        expect((await remove).method()).toBe('GET');
        await expect(rows).toHaveCount(1);
    });

    test('el campo se llama por su etiqueta y la pista solo lo describe', async ({ ui }) => {
        const input = ui
            .section('upload')
            .locator('[x-data="upload"]')
            .first()
            .locator('input[type="file"]');

        await expect(input).toHaveAccessibleName(
            'Arrastra aquí el documento o pulsa para elegirlo',
        );
        await expect(input).toHaveAccessibleDescription(
            'PDF, imágenes o ZIP · hasta 1 MB por archivo',
        );
    });

    test('arrastrar sobre la zona la marca y soltar fuera la desmarca', async ({ ui }) => {
        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        const label = zone.locator('label');

        await expect(zone).toHaveAttribute('data-state', 'idle');

        await label.dispatchEvent('dragenter');
        await expect(zone).toHaveAttribute('data-state', 'dragging');

        await label.dispatchEvent('dragleave');
        await expect(zone).toHaveAttribute('data-state', 'idle');
    });

    test('soltar un archivo sobre la zona lo sube', async ({ page, ui }) => {
        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        const rows = zone.getByRole('listitem');
        const post = page.waitForRequest(
            (request) => request.method() === 'POST' && request.url().includes('/_ui/demo/upload'),
        );
        const dataTransfer = await page.evaluateHandle(() => {
            const transfer = new DataTransfer();

            transfer.items.add(new File(['%PDF-1.4'], 'soltado.pdf', { type: 'application/pdf' }));

            return transfer;
        });

        await zone.locator('label').dispatchEvent('dragenter');
        await expect(zone).toHaveAttribute('data-state', 'dragging');
        await zone.locator('label').dispatchEvent('drop', { dataTransfer });

        await post;
        await expect(zone).toHaveAttribute('data-state', 'idle');
        await expect(rows).toHaveCount(3);
        await expect(rows.last()).toContainText('soltado.pdf');
        await expect(rows.last()).toContainText('Subido', { timeout: 10_000 });
        await expect(zone.locator('input[type="hidden"][name="uploaded[]"]')).toHaveCount(1);
    });

    test('una subida que falla se reintenta hasta subir', async ({ page, ui }) => {
        const unexpected = expectingStatus(page, 500);
        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        const row = zone.getByRole('listitem').last();
        const hidden = zone.locator('input[type="hidden"][name="uploaded[]"]');
        const failing = async (route) => {
            if (route.request().method() === 'POST') {
                await route.fulfill({ status: 500, contentType: 'text/plain', body: '' });
            } else {
                await route.continue();
            }
        };

        await page.route('**/_ui/demo/upload', failing);
        await zone.locator('input[type="file"]').setInputFiles([pdf('informe.pdf')]);

        await expect(row).toContainText('No se ha podido subir');
        await expect(zone.getByRole('alert')).toHaveText('informe.pdf: no se ha podido subir');
        await expect(
            page.getByRole('region', { name: 'Avisos' }).filter({ hasText: 'informe.pdf' }),
        ).toHaveCount(0);
        await expect(hidden).toHaveCount(0);

        await page.unroute('**/_ui/demo/upload', failing);
        await zone.getByRole('button', { name: 'Reintentar informe.pdf' }).click();

        await expect(row).toContainText('Subido', { timeout: 10_000 });
        await expect(zone.getByRole('button', { name: 'Reintentar informe.pdf' })).toBeHidden();
        await expect(zone.getByRole('alert')).toBeHidden();
        await expect(hidden).toHaveCount(1);
        expect(unexpected, 'Errores en la consola del navegador').toEqual([]);
    });

    test('solo cuenta como subido el id que el servidor devuelve en texto plano', async ({
        page,
        ui,
    }) => {
        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        const row = zone.getByRole('listitem').last();
        const hidden = zone.locator('input[type="hidden"][name="uploaded[]"]');
        // A 200 that is not the id: the page of a proxy and an empty body
        const answers = [
            { status: 200, contentType: 'text/html', body: '<!doctype html><title>Portal</title>' },
            { status: 200, contentType: 'text/plain', body: '  ' },
        ];

        await page.route('**/_ui/demo/upload', async (route) => {
            if (route.request().method() !== 'POST') {
                await route.continue();

                return;
            }

            await route.fulfill(answers.shift());
        });

        await zone.locator('input[type="file"]').setInputFiles([pdf('portal.pdf')]);

        await expect(row).toContainText('No se ha podido subir');
        await expect(zone.getByRole('alert')).toHaveText('portal.pdf: no se ha podido subir');
        await expect(hidden).toHaveCount(0);

        await zone.getByRole('button', { name: 'Reintentar portal.pdf' }).click();

        await expect.poll(() => answers.length).toBe(0);
        await expect(row).toContainText('No se ha podido subir');
        await expect(zone.getByRole('alert')).toHaveText('portal.pdf: no se ha podido subir');
        await expect(hidden).toHaveCount(0);
    });

    test('el motivo corto de un 400 llega a la fila y a la alerta', async ({ page, ui }) => {
        const unexpected = expectingStatus(page, 400);
        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        const row = zone.getByRole('listitem').last();

        await page.route('**/_ui/demo/upload', async (route) => {
            if (route.request().method() !== 'POST') {
                await route.continue();

                return;
            }

            // What the upload controller answers to an invalid file
            await route.fulfill({
                status: 400,
                contentType: 'text/html; charset=UTF-8',
                body: 'Archivo inválido',
            });
        });

        await zone.locator('input[type="file"]').setInputFiles([pdf('roto.pdf')]);

        await expect(row).toContainText('Archivo inválido');
        await expect(zone.getByRole('alert')).toHaveText('roto.pdf: archivo inválido');
        await expect(zone.getByRole('button', { name: 'Reintentar roto.pdf' })).toBeVisible();
        expect(unexpected, 'Errores en la consola del navegador').toEqual([]);
    });

    test('una subida que deja de avanzar se da por perdida', async ({ page, ui }) => {
        await page.clock.install();
        await page.goto('/_ui#ui-upload');

        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        const row = zone.getByRole('listitem').last();

        // The endpoint never answers
        await page.route('**/_ui/demo/upload', () => {});
        await zone.locator('input[type="file"]').setInputFiles([pdf('lento.pdf')]);

        await expect(row).toContainText('Subiendo');
        await expect(zone).toHaveAttribute('aria-busy', 'true');

        await page.clock.runFor(61_000);

        await expect(row).toContainText('La subida se ha detenido');
        await expect(zone.getByRole('alert')).toHaveText('lento.pdf: la subida se ha detenido');
        await expect(zone.getByRole('button', { name: 'Reintentar lento.pdf' })).toBeVisible();
        await expect(zone).toHaveAttribute('aria-busy', 'false');
    });

    test('la zona de un solo archivo rechaza el segundo', async ({ ui }) => {
        const zone = ui.section('upload').locator('[x-data="upload"]').nth(1);
        const input = zone.locator('input[type="file"]');
        const rows = zone.getByRole('listitem');

        await expect(input).not.toHaveAttribute('multiple', '');

        await input.setInputFiles([
            {
                name: 'alumnos.xlsx',
                mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                buffer: Buffer.alloc(2048, 'x'),
            },
        ]);

        await expect(rows).toHaveCount(1);
        await expect(rows.first()).toContainText('Subido', { timeout: 10_000 });

        await input.setInputFiles([
            {
                name: 'otros.xlsx',
                mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                buffer: Buffer.alloc(2048, 'x'),
            },
        ]);

        await expect(zone.getByRole('alert')).toHaveText('Solo se admite un archivo');
        await expect(rows).toHaveCount(1);
    });

    test('el formulario espera a que termine la subida y después envía el oculto', async ({
        page,
        ui,
    }) => {
        const form = ui.section('form').locator('#evidence-form');
        const zone = form.locator('[x-data="upload"]');
        const row = zone.getByRole('listitem').last();
        const save = form.locator('button[type="submit"]');
        const submits = [];
        let release;
        const held = new Promise((resolve) => {
            release = resolve;
        });

        await page.route('**/_ui/demo/upload', async (route) => {
            if (route.request().method() !== 'POST') {
                await route.continue();

                return;
            }

            await held;
            await route.fulfill({ status: 200, contentType: 'text/plain', body: 'demo-retenido' });
        });
        await page.route('**/_ui/demo/submit', async (route) => {
            submits.push(route.request().postData() ?? '');
            await route.fulfill({ status: 204 });
        });

        await zone.locator('input[type="file"]').setInputFiles([pdf('informe.pdf')]);
        await expect(row).toContainText('Subiendo');
        await expect(zone).toHaveAttribute('aria-busy', 'true');

        await save.click();

        // The submit waits: no request, a message in the zone and the form still free
        await expect(zone.getByRole('alert')).toHaveText('Espera a que termine la subida');
        await expect(form).toHaveAttribute('aria-busy', 'false');
        await expect(save).toBeEnabled();
        await expect(save).toBeFocused();
        expect(submits).toHaveLength(0);

        // A second try says it again: the text changes by a silent no-break space, so the alert is read anew
        const alertText = () =>
            zone
                .getByRole('alert')
                .locator('span')
                .evaluate((node) => node.textContent);

        expect(await alertText()).toBe('Espera a que termine la subida');
        await save.click();
        await expect.poll(alertText).toBe('Espera a que termine la subida\u00A0');
        expect(submits).toHaveLength(0);

        release();

        await expect(row).toContainText('Subido');
        await expect(zone).toHaveAttribute('aria-busy', 'false');
        await expect(zone.getByRole('alert')).toBeHidden();

        await save.click();

        await expect.poll(() => submits.length).toBe(1);
        expect(submits[0]).toContain('name="evidence_files[]"');
        expect(submits[0]).toContain('demo-retenido');
    });

    test('en móvil la zona y los botones de las filas miden al menos 44 px', async ({
        page,
        ui,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'Los 44 px se exigen con puntero grueso');

        expect(await page.evaluate(() => matchMedia('(pointer: coarse)').matches)).toBe(true);

        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        const area = zone.locator('input[type="file"]').locator('xpath=..');
        const buttons = zone.getByRole('listitem').getByRole('button');
        const box = await area.boundingBox();

        expect(box.height, 'Zona de subida').toBeGreaterThanOrEqual(44);
        expect(box.width, 'Zona de subida').toBeGreaterThanOrEqual(44);
        expect(await buttons.count()).toBeGreaterThan(1);

        for (let i = 0; i < (await buttons.count()); i++) {
            const button = buttons.nth(i);

            if (!(await button.isVisible())) {
                continue;
            }

            const size = await button.boundingBox();
            const name = await button.getAttribute('aria-label');

            expect(size.height, name).toBeGreaterThanOrEqual(44);
            expect(size.width, name).toBeGreaterThanOrEqual(44);
        }

        await ui.noOverflow();
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-upload')).toEqual([]);
    });
});
