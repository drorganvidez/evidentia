import { expect, test } from '../fixtures/ui.js';

const pdf = (name, kilobytes = 8) => ({
    name,
    mimeType: 'application/pdf',
    buffer: Buffer.alloc(kilobytes * 1024, 'a'),
});

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

    test('arrastrar sobre la zona la marca y soltar fuera la desmarca', async ({ ui }) => {
        const zone = ui.section('upload').locator('[x-data="upload"]').first();
        const label = zone.locator('label');

        await expect(zone).toHaveAttribute('data-state', 'idle');

        await label.dispatchEvent('dragenter');
        await expect(zone).toHaveAttribute('data-state', 'dragging');

        await label.dispatchEvent('dragleave');
        await expect(zone).toHaveAttribute('data-state', 'idle');
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

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-upload')).toEqual([]);
    });
});
