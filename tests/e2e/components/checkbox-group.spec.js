import { expect, test } from '../fixtures/ui.js';

test.describe('Casillas y grupos de casillas', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-checkbox-group');
    });

    test('marcar Coordinador muestra el comité y desmarcarlo lo oculta', async ({ ui }) => {
        const group = ui.section('checkbox-group').getByRole('group', { name: /^Roles/ });
        const coordinator = group.getByRole('checkbox', { name: /Coordinador Valida/ });
        const secretary = group.getByRole('checkbox', { name: /Secretario/ });
        const committee = group.getByRole('combobox', { name: 'Comité' });

        await expect(group.getByRole('checkbox', { name: /Estudiante/ })).toBeChecked();
        await expect(committee).toBeHidden();

        await coordinator.check();
        await expect(committee).toBeVisible();

        await coordinator.uncheck();
        await expect(committee).toBeHidden();

        await secretary.check();
        await expect(committee).toBeVisible();
        await committee.selectOption('2');
        await expect(committee).toHaveValue('2');
    });

    test('las casillas, los radios y los conmutadores cambian con el teclado y el ratón', async ({
        ui,
    }) => {
        const section = ui.section('choice');
        const remember = section.getByRole('checkbox', { name: /Recordarme/ });
        const online = section.getByRole('radio', { name: /En línea/ });
        const profile = section.getByRole('switch', { name: 'Perfil público' });

        await expect(remember).not.toBeChecked();
        await remember.check();
        await expect(remember).toBeChecked();

        await remember.focus();
        await remember.press('Space');
        await expect(remember).not.toBeChecked();

        await expect(section.getByRole('radio', { name: /Presencial/ })).toBeChecked();
        await online.check();
        await expect(online).toBeChecked();
        await expect(section.getByRole('radio', { name: /Presencial/ })).not.toBeChecked();

        await expect(profile).not.toBeChecked();
        await profile.click();
        await expect(profile).toBeChecked();
        await expect(profile).toHaveAttribute('role', 'switch');

        await expect(
            section.getByRole('checkbox', { name: 'Opción deshabilitada' }),
        ).toBeDisabled();
        await expect(
            section.getByRole('switch', { name: 'Conmutador deshabilitado' }),
        ).toBeDisabled();
    });

    test('cada casilla ofrece un objetivo de al menos 44 px en móvil', async ({ ui }, testInfo) => {
        test.skip(
            testInfo.project.name !== 'mobile',
            'El objetivo de 44 px se exige con puntero grueso',
        );

        const inputs = ui.section('choice').locator('input');
        const count = await inputs.count();

        expect(count).toBeGreaterThan(8);

        for (let i = 0; i < count; i++) {
            const box = await inputs.nth(i).boundingBox();

            expect(box).not.toBeNull();
            expect(box.height).toBeGreaterThanOrEqual(44);
            expect(box.width).toBeGreaterThanOrEqual(44);
        }

        await ui.noOverflow();
    });

    test('las dos secciones cumplen WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-choice')).toEqual([]);
        expect(await ui.axe('#ui-checkbox-group')).toEqual([]);
    });
});
