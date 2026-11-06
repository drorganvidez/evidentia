import { expect, test } from '../fixtures/ui.js';

// The console guard of the fixture turns any "CSP Parser Error" into a failure.
test.describe('Sonda de la gramática CSP de Alpine', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-csp');
    });

    test('bucles anidados, índices, x-model en ítems y comparaciones', async ({ ui }) => {
        const loops = ui.section('csp').locator('[data-probe="loops"]');
        const summary = loops.locator('[data-probe="summary"]');
        const rows = loops.locator('ol > li');

        await expect(rows).toHaveCount(2);
        await expect(rows.nth(1).locator('span.font-mono').first()).toHaveText('2');
        await expect(loops.getByText('veinte').nth(1)).toBeVisible();
        await expect(loops.getByText('veinte').first()).toBeHidden();
        await expect(
            loops.getByRole('button', { name: 'Quitar', exact: true }).first(),
        ).toBeDisabled();
        await expect(summary).toHaveText('Uno:10:Hijo 1|Hijo 2 / Dos:20:');

        await loops.getByLabel('Hijo').first().fill('Hija 1');
        await expect(summary).toHaveText('Uno:10:Hija 1|Hijo 2 / Dos:20:');

        await loops.getByRole('button', { name: 'Quitar hijo' }).first().click();
        await expect(summary).toHaveText('Uno:10:Hijo 2 / Dos:20:');

        await loops.getByLabel('Duración').nth(1).fill('30');
        await expect(summary).toHaveText('Uno:10:Hijo 2 / Dos:30:');
        await expect(loops.getByText('veinte').nth(1)).toBeHidden();

        await loops.getByRole('button', { name: 'Quitar', exact: true }).nth(1).click();
        await expect(rows).toHaveCount(1);

        await loops.getByLabel('Nuevo').fill('abc');
        await expect(loops.locator('[data-probe="filtered"]')).toHaveText('3');
        await loops.getByLabel('Nuevo').press('Enter');
        await expect(rows).toHaveCount(2);
    });

    test('store, dispatch, ternarios, estilo por objeto, modificadores, magics y radios', async ({
        page,
        ui,
    }) => {
        const misc = ui.section('csp').locator('[data-probe="misc"]');

        await misc.getByRole('button', { name: 'Tema oscuro' }).click();
        await expect(misc.locator('[data-probe="store"]')).toHaveText('dark');
        await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
        await misc.getByRole('button', { name: 'Tema sistema' }).click();
        await expect(misc.locator('[data-probe="store"]')).toHaveText('system');

        await misc.getByRole('button', { name: 'Avisar' }).click();
        await expect(misc.locator('[data-probe="last"]')).toHaveText('Guardado');

        const toggle = misc.getByRole('button', { name: 'Desplegar' });
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await toggle.click();
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
        await expect(misc.locator('[data-probe="panel"]')).toBeVisible();
        await expect(misc.locator('[data-probe="panel"]')).toHaveAttribute('id', /^panel-/);
        await ui.section('csp').getByRole('heading', { name: 'Sonda CSP' }).click();
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');

        await misc.getByRole('button', { name: 'Sumar' }).click();
        await misc.getByRole('button', { name: 'Sumar' }).click();
        await expect(misc.locator('[data-probe="count"]')).toHaveText('2');
        await expect(misc.locator('[data-probe="count"]')).toHaveClass(/text-ink/);
        expect(await misc.locator('[data-probe="bar"]').evaluate((el) => el.style.width)).toBe(
            '20%',
        );

        await misc.getByRole('button', { name: 'Abrir diálogo' }).click();
        await expect(misc.locator('dialog')).toHaveAttribute('open', '');
        await misc.getByRole('button', { name: 'Cerrar' }).click();
        await expect(misc.locator('dialog')).not.toHaveAttribute('open', '');

        await page.keyboard.press('k');
        await expect(misc.locator('[data-probe="last"]')).toHaveText('tecla k');

        await misc.getByRole('radio', { name: 'Oscuro' }).check();
        await expect(misc.locator('[data-probe="mode"]')).toHaveText('dark');
        await expect(misc.getByRole('radio', { name: 'Oscuro' })).toBeChecked();
    });
});
