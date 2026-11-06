import { choose, expect, test } from '../fixtures/ui.js';

test.describe('Campos', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-field');
    });

    test('el ojo alterna el tipo y el estado con el mismo nombre sin perder el foco', async ({
        ui,
    }) => {
        const section = ui.section('field');
        const input = section.locator('#field-secret');
        const toggle = input.locator('xpath=..').getByRole('button');

        await expect(input).toHaveAttribute('type', 'password');
        await expect(toggle).toHaveAccessibleName('Mostrar la contraseña');
        await expect(toggle).toHaveAttribute('aria-pressed', 'false');

        await toggle.click();

        // A toggle button keeps its name: «Mostrar la contraseña, pulsado» means it is shown
        await expect(input).toHaveAttribute('type', 'text');
        await expect(toggle).toHaveAttribute('aria-pressed', 'true');
        await expect(toggle).toHaveAccessibleName('Mostrar la contraseña');
        await expect(toggle).toHaveAttribute('title', 'Mostrar la contraseña');
        await expect(toggle).toBeFocused();

        await toggle.press('Enter');

        await expect(input).toHaveAttribute('type', 'password');
        await expect(toggle).toHaveAttribute('aria-pressed', 'false');
        await expect(toggle).toHaveAccessibleName('Mostrar la contraseña');
        await expect(toggle).toBeFocused();
    });

    test('el contador sigue lo que se escribe', async ({ ui }) => {
        const section = ui.section('field');
        const area = section.locator('#field-notes');
        const counter = section.locator('#field-notes-count');

        const initial = await area.inputValue();

        expect(initial.length).toBeGreaterThan(0);
        await expect(counter).toHaveText(`${initial.length} de 300 caracteres`);
        await expect(area).toHaveAttribute('aria-describedby', 'field-notes-count');

        await area.fill('Hola');

        await expect(counter).toHaveText('4 de 300 caracteres');

        await area.fill('x'.repeat(300));

        await expect(counter).toHaveText('300 de 300 caracteres');
        await expect(area).toHaveValue('x'.repeat(300));
    });

    test('el select con placeholder y la pareja de horas y minutos', async ({ ui }) => {
        const section = ui.section('field');
        const committee = section.getByRole('combobox', { name: 'Comité', exact: true });
        const select = section.locator('select[name="committee_id"]');

        await expect(committee).toHaveValue('');
        await expect(committee).toHaveAttribute('placeholder', 'Elige un comité');
        await expect(select).toHaveValue('');
        await choose(committee, 'Logística');
        await expect(select).toHaveValue('2');

        await expect(
            section.getByRole('group', { name: 'Duración' }).getByLabel('Horas'),
        ).toHaveValue('1');
        await expect(
            section.getByRole('group', { name: 'Duración' }).getByLabel('Minutos'),
        ).toHaveValue('30');
    });

    test('los controles miden 16 px en móvil para que iOS no amplíe', async ({ ui }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'Comprobación del tramo móvil');

        const section = ui.section('field');
        const controls = section.locator('input, select, textarea');
        const count = await controls.count();

        expect(count).toBeGreaterThan(10);

        for (let i = 0; i < count; i++) {
            const control = controls.nth(i);
            const size = await control.evaluate((el) => getComputedStyle(el).fontSize);
            const name = await control.evaluate((el) => el.name);

            expect(size, `Tamaño de «${name}»`).toBe('16px');
        }

        await ui.noOverflow();
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-field')).toEqual([]);
    });
});
