import { expect, test } from '../fixtures/ui.js';

test.describe('Formularios', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-form');
    });

    test('el resumen de errores enlaza con los campos y es lo único con autofocus', async ({
        page,
        ui,
    }) => {
        const section = ui.section('form');
        const summary = section.getByRole('group', { name: 'Revisa el formulario' });

        await expect(summary).toBeVisible();
        await expect(summary).toHaveAttribute('tabindex', '-1');
        expect(await page.locator('[autofocus]').count()).toBe(1);

        await summary.getByRole('link', { name: /^Título/ }).click();

        await expect(section.locator('#field-title')).toBeFocused();
    });

    test('los formularios llevan el token, el método y la validación del servidor', async ({
        ui,
    }) => {
        const section = ui.section('form');
        const evidence = section.locator('#evidence-form');

        await expect(evidence).toHaveAttribute('method', 'post');
        await expect(evidence).toHaveAttribute('enctype', 'multipart/form-data');
        await expect(evidence).toHaveAttribute('novalidate', '');
        await expect(evidence).toHaveAttribute('aria-busy', 'false');
        await expect(evidence.locator('input[name="_token"]')).toHaveCount(1);

        const search = section.locator('#search-form');

        await expect(search).toHaveAttribute('method', 'get');
        await expect(search.locator('input[name="_token"]')).toHaveCount(0);
    });

    test('al enviar el botón pasa a ocupado y un segundo envío no sale', async ({ page, ui }) => {
        let requests = 0;

        await page.route(
            (url) => url.pathname === '/_ui' && url.searchParams.get('export') === 'csv',
            async (route) => {
                requests += 1;
                await new Promise((resolve) => setTimeout(resolve, 400));
                await route.fulfill({
                    status: 200,
                    contentType: 'text/csv',
                    headers: { 'Content-Disposition': 'attachment; filename="export.csv"' },
                    body: 'a;b\n1;2\n',
                });
            },
        );

        const form = ui.section('form').locator('#export-form');
        const button = form.locator('button[type="submit"]');
        const download = page.waitForEvent('download');

        await expect(button).toHaveText('Descargar CSV');
        await button.click();

        await expect(form).toHaveAttribute('aria-busy', 'true');
        await expect(button).toHaveAttribute('aria-busy', 'true');
        await expect(button).toHaveText('Preparando');
        await expect(button).toBeDisabled();

        // A second submit while busy is swallowed by the component
        await form.evaluate((element) => element.requestSubmit());
        await download;

        expect(requests).toBe(1);
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-form')).toEqual([]);
    });
});
