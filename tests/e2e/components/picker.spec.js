import { expect, test } from '../fixtures/ui.js';

// The status region of the attendees picker, not the first one the section happens to have
const status = (section) => section.locator('#attendees-form [x-data="picker"] > p[role="status"]');

test.describe('Selector de personas', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-picker');
    });

    test('escribir filtra, las flechas e Intro eligen y el select nativo lo refleja', async ({
        page,
        ui,
    }) => {
        const section = ui.section('picker');
        const input = section.getByRole('combobox', { name: 'Asistentes' });
        const chips = section.getByRole('list', { name: 'Seleccionados' }).getByRole('listitem');
        const native = section.locator('#field-attendees-native');
        const listbox = section.locator('#field-attendees-list');
        const selectedValues = () =>
            native.evaluate((select) =>
                Array.from(select.selectedOptions).map((option) => option.value),
            );

        await expect(chips).toHaveCount(3);
        await expect(native).toHaveAttribute('aria-hidden', 'true');
        expect(await selectedValues()).toEqual(['2', '5', '9']);
        await expect(input).toHaveAttribute('aria-expanded', 'false');

        await input.fill('garc');

        await expect(input).toHaveAttribute('aria-expanded', 'true');
        await expect(listbox).toBeVisible();

        const options = listbox.getByRole('option');
        const count = await options.count();

        expect(count).toBeGreaterThan(1);

        for (let i = 0; i < count; i++) {
            await expect(options.nth(i)).toContainText(/García/);
        }

        await expect(options.first()).toHaveAttribute('data-active', '');
        await input.press('ArrowDown');
        await expect(options.nth(1)).toHaveAttribute('data-active', '');
        await expect(input).toHaveAttribute('aria-activedescendant', /field-attendees-option-/);

        const label = await options.nth(1).locator('span > span').first().textContent();

        await input.press('Enter');

        await expect(chips).toHaveCount(4);
        await expect(chips.last()).toContainText(label.trim());
        await expect(input).toHaveValue('');
        await expect(input).toBeFocused();
        expect(await selectedValues()).toHaveLength(4);
        await expect(status(section)).toContainText('4 personas seleccionadas');

        await input.press('Escape');
        await expect(listbox).toBeHidden();
        await expect(input).toHaveAttribute('aria-expanded', 'false');

        await chips
            .last()
            .getByRole('button', { name: `Quitar a ${label.trim()}` })
            .click();

        await expect(chips).toHaveCount(3);
        expect(await selectedValues()).toEqual(['2', '5', '9']);

        await page.keyboard.press('Backspace');
        await expect(chips).toHaveCount(2);
        expect(await selectedValues()).toEqual(['2', '5']);
    });

    test('una lista predeterminada pide confirmación y sustituye la selección', async ({ ui }) => {
        const section = ui.section('picker');
        const chips = section.getByRole('list', { name: 'Seleccionados' }).getByRole('listitem');
        const load = section.getByRole('button', { name: 'Cargar' });

        await expect(load).toBeDisabled();

        await section.getByLabel('Lista predeterminada').selectOption('0');
        await expect(load).toBeEnabled();
        // A click from script skips the input pipeline that lets Chromium paint before the timers, so the
        // focus has to wait for the frame that shows the confirmation, as in Firefox and Safari
        await load.evaluate((button) => button.click());

        const confirm = section.getByRole('group', { name: 'Confirmar la carga' });

        await expect(confirm).toBeVisible();
        await expect(confirm).toContainText(
            'Sustituirá la selección actual (3 personas) por Comité de Programa (12)',
        );
        await expect(confirm.getByRole('button', { name: 'Cancelar' })).toBeFocused();

        await confirm.getByRole('button', { name: 'Cancelar' }).click();
        await expect(confirm).toBeHidden();
        await expect(chips).toHaveCount(3);

        await load.click();
        await confirm.getByRole('button', { name: 'Sustituir' }).click();

        await expect(confirm).toBeHidden();
        await expect(chips).toHaveCount(12);
        await expect(status(section)).toHaveText('Lista cargada, 12 personas seleccionadas');
    });

    test('el formulario envía exactamente los identificadores elegidos', async ({ page, ui }) => {
        const section = ui.section('picker');
        let sent = null;

        await page.route(
            (url) => url.pathname === '/_ui' && url.searchParams.has('attendees[]'),
            async (route) => {
                sent = new URL(route.request().url()).searchParams.getAll('attendees[]');
                await route.fulfill({ status: 204 });
            },
        );

        await section.getByRole('button', { name: 'Guardar asistentes' }).click();

        await expect.poll(() => sent).toEqual(['2', '5', '9']);
    });

    test('con una sola elección el nombre queda en la caja y el select nativo guarda el id', async ({
        ui,
    }) => {
        const section = ui.section('picker');
        const input = section.getByRole('combobox', { name: 'Alumno' });
        const native = section.locator('#field-student-native');

        await expect(native).toHaveValue('');

        await input.fill('lopez');
        await input.press('ArrowDown');
        await input.press('Enter');

        const value = await input.inputValue();

        expect(value).toMatch(/López/);
        expect(await native.inputValue()).not.toBe('');
        await expect(section.locator('#field-student-list')).toBeHidden();

        await input.fill('zzzz');
        await expect(
            section.locator('#field-student-list').locator('xpath=..').getByText('Sin resultados'),
        ).toBeVisible();
        await input.press('Escape');
        await input.press('Escape');
        await expect(input).toHaveValue(value);
    });

    test('en móvil los chips y sus botones miden al menos 44 px', async ({
        page,
        ui,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'Los 44 px se exigen con puntero grueso');

        expect(await page.evaluate(() => matchMedia('(pointer: coarse)').matches)).toBe(true);

        const chips = ui
            .section('picker')
            .getByRole('list', { name: 'Seleccionados' })
            .getByRole('listitem');

        await expect(chips).toHaveCount(3);

        for (let i = 0; i < 3; i++) {
            const button = chips.nth(i).getByRole('button');
            const size = await button.boundingBox();
            const name = await button.getAttribute('aria-label');

            expect(
                (await chips.nth(i).boundingBox()).height,
                `Chip ${i + 1}`,
            ).toBeGreaterThanOrEqual(44);
            expect(size.height, name).toBeGreaterThanOrEqual(44);
            expect(size.width, name).toBeGreaterThanOrEqual(44);
        }

        await ui.noOverflow();
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-picker')).toEqual([]);
    });
});
