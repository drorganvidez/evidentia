import { expect, test } from '../fixtures/ui.js';

test.describe('Selector de iconos', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-icon-picker');
    });

    test('buscar filtra por nombre o alias y elegir cambia la previsualización', async ({ ui }) => {
        const picker = ui.section('icon-picker').getByRole('group', { name: /^Icono del comité/ });
        const search = picker.getByRole('searchbox', { name: 'Buscar icono' });
        const visible = picker.locator('label[x-show]:visible');
        const preview = picker.locator('[x-ref="preview"]');
        const value = picker.locator('[x-text="value || \'Sin icono\'"]');

        expect(await visible.count()).toBeGreaterThan(90);
        await expect(value).toHaveText('Sin icono');
        await expect(preview.locator('svg')).toHaveCount(0);

        await search.fill('finanzas');

        await expect(visible).toHaveCount(1);
        await expect(picker.getByRole('status')).toHaveText('1 icono');

        const euro = picker.getByRole('radio', { name: /^euro,/ });

        await euro.check();

        await expect(euro).toBeChecked();
        await expect(value).toHaveText('euro');
        await expect(preview.locator('svg')).toHaveCount(1);

        await search.fill('dinero');

        await expect(visible).toHaveCount(4);
        await expect(picker.getByRole('status')).toHaveText('4 iconos');

        await search.fill('');

        await expect.poll(() => visible.count()).toBeGreaterThan(90);
    });

    test('las flechas recorren solo los iconos visibles', async ({ ui }) => {
        const picker = ui.section('icon-picker').getByRole('group', { name: /^Icono del comité/ });
        const search = picker.getByRole('searchbox', { name: 'Buscar icono' });
        const value = picker.locator('[x-text="value || \'Sin icono\'"]');

        await search.fill('dinero');
        await picker.getByRole('radio', { name: /^piggy-bank/ }).check();
        await expect(value).toHaveText('piggy-bank');

        await picker.getByRole('radio', { name: /^piggy-bank/ }).press('ArrowRight');

        await expect(picker.getByRole('radio', { name: /^euro,/ })).toBeChecked();
        await expect(picker.getByRole('radio', { name: /^euro,/ })).toBeFocused();
        await expect(value).toHaveText('euro');
    });

    test('un valor inicial llega marcado y con su previsualización', async ({ ui }) => {
        const picker = ui.section('icon-picker').getByRole('group', { name: 'Icono del evento' });

        await expect(picker.getByRole('radio', { name: /^megaphone/ })).toBeChecked();
        await expect(picker.locator('[x-ref="preview"] svg')).toHaveCount(1);
        await expect(picker.locator('[x-text="value || \'Sin icono\'"]')).toHaveText('megaphone');
    });

    test('cada icono ofrece un objetivo de 44 px y la sección no desborda en móvil', async ({
        ui,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'Comprobación del tramo móvil');

        const radios = ui.section('icon-picker').getByRole('radio');
        const count = await radios.count();

        expect(count).toBeGreaterThan(200);

        for (const index of [0, 7, 50, count - 1]) {
            const box = await radios.nth(index).boundingBox();

            expect(box).not.toBeNull();
            expect(box.width).toBeGreaterThanOrEqual(44);
            expect(box.height).toBeGreaterThanOrEqual(44);
        }

        await ui.noOverflow();
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-icon-picker')).toEqual([]);
    });
});
