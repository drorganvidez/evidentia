import { choose, expect, test } from '../fixtures/ui.js';

const field = (section, name) => section.getByRole('combobox', { name, exact: true });

/** The listbox a combobox controls. */
async function listbox(combobox) {
    return combobox.page().locator(`[id="${await combobox.getAttribute('aria-controls')}"]`);
}

/** Counts the change events the native select fires from now on. */
async function countChanges(select) {
    await select.evaluate((element) => {
        element.dataset.changes = '0';
        element.addEventListener('change', () => {
            element.dataset.changes = String(Number(element.dataset.changes) + 1);
        });
    });

    return () => select.getAttribute('data-changes');
}

test.describe('Desplegables', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-field');
    });

    test('el teclado abre, busca sin mayúsculas ni acentos, recorre y elige', async ({ ui }) => {
        const section = ui.section('field');
        const province = field(section, 'Provincia');
        const native = section.locator('select#field-province');
        const list = await listbox(province);
        const options = list.getByRole('option');
        const changes = await countChanges(native);

        await expect(native).toHaveAttribute('aria-hidden', 'true');
        await expect(native).toHaveAttribute('tabindex', '-1');
        await expect(province).toHaveValue('');
        await expect(province).toHaveAttribute('placeholder', 'Elige una provincia');
        await expect(province).toHaveAttribute('aria-expanded', 'false');

        await province.focus();
        await province.press('ArrowDown');

        await expect(province).toHaveAttribute('aria-expanded', 'true');
        await expect(list).toBeVisible();
        await expect(options).toHaveCount(50);
        await expect(options.first()).toHaveAttribute('data-active', '');
        await expect(province).toHaveAttribute(
            'aria-activedescendant',
            'field-province-combobox-option-1',
        );

        await province.press('ArrowDown');
        await expect(options.nth(1)).toHaveAttribute('data-active', '');
        await province.press('End');
        await expect(options.last()).toHaveAttribute('data-active', '');
        await expect(options.last()).toBeInViewport();
        await province.press('Home');
        await expect(options.first()).toHaveAttribute('data-active', '');

        await province.pressSequentially('CADIZ');

        await expect(options).toHaveCount(1);
        await expect(options.first()).toHaveText('Cádiz');
        await expect(options.first()).toHaveAttribute('data-active', '');

        await province.press('Enter');

        await expect(province).toHaveValue('Cádiz');
        await expect(province).toHaveAttribute('aria-expanded', 'false');
        await expect(province).not.toHaveAttribute('aria-activedescendant');
        await expect(province).toBeFocused();
        await expect(native).toHaveValue('Cádiz');
        expect(await changes()).toBe('1');

        // Reopened, the chosen option is marked and active; Escape closes and puts the label back
        await province.press('ArrowDown');
        await expect(options).toHaveCount(50);

        const chosen = list.getByRole('option', { name: 'Cádiz', exact: true });

        await expect(chosen).toHaveAttribute('aria-selected', 'true');
        await expect(chosen).toHaveAttribute('data-active', '');
        await expect(list.locator('[aria-selected="true"]')).toHaveCount(1);

        await province.press('ArrowDown');
        await province.press('Escape');

        await expect(list).toBeHidden();
        await expect(province).toHaveValue('Cádiz');
        await expect(province).toBeFocused();
        await expect(native).toHaveValue('Cádiz');
        expect(await changes()).toBe('1');
    });

    test('sin resultados lo dice, Intro no elige y Escape devuelve la etiqueta', async ({ ui }) => {
        const section = ui.section('field');
        const province = field(section, 'Provincia');
        const list = await listbox(province);

        await province.click();
        await province.pressSequentially('xyz');

        await expect(list.getByRole('option')).toHaveCount(0);
        await expect(list.locator('xpath=..').getByText('Sin resultados')).toBeVisible();

        await province.press('Enter');
        await expect(province).toHaveAttribute('aria-expanded', 'true');

        await province.press('Escape');
        await expect(province).toHaveAttribute('aria-expanded', 'false');
        await expect(province).toHaveValue('');
        await expect(section.locator('select#field-province')).toHaveValue('');
    });

    test('el ratón abre con la opción actual marcada y elige con un clic', async ({ page, ui }) => {
        const section = ui.section('field');
        const state = field(section, 'Estado');
        const native = section.locator('select#field-session-state');
        const list = await listbox(state);
        const changes = await countChanges(native);

        await expect(state).toHaveValue('Abierta');

        await state.click();

        // The box is emptied for a search and the chosen label waits in the placeholder
        await expect(list).toBeVisible();
        await expect(state).toHaveValue('');
        await expect(state).toHaveAttribute('placeholder', 'Abierta');
        await expect(list.getByRole('option', { name: 'Abierta' })).toHaveAttribute(
            'aria-selected',
            'true',
        );
        await expect(list.getByRole('option', { name: 'Cerrada' })).toHaveAttribute(
            'aria-selected',
            'false',
        );

        await list.getByRole('option', { name: 'Cerrada' }).click();

        await expect(list).toBeHidden();
        await expect(state).toHaveValue('Cerrada');
        await expect(native).toHaveValue('closed');
        expect(await changes()).toBe('1');

        // A click outside closes without choosing
        await state.click();
        await expect(list).toBeVisible();
        await page.locator('#ui-field-title').click();
        await expect(list).toBeHidden();
        await expect(state).toHaveValue('Cerrada');

        // The label points to the combobox
        await section.locator('label[for="field-session-state-combobox"]').click();
        await expect(state).toBeFocused();
    });

    test('las opciones deshabilitadas no se eligen y las flechas las saltan', async ({ ui }) => {
        const section = ui.section('field');
        const audience = field(section, 'Dirigido a');
        const list = await listbox(audience);
        const native = section.locator('select#field-audience');

        await expect(audience).toHaveValue('Todos');

        await audience.focus();
        await audience.press('ArrowDown');
        await expect(list.getByRole('option', { name: 'Todos' })).toHaveAttribute(
            'data-active',
            '',
        );
        await audience.press('ArrowDown');
        await expect(list.getByRole('option', { name: 'Estudiantes' })).toHaveAttribute(
            'data-active',
            '',
        );
        await audience.press('ArrowDown');

        const lecturers = list.getByRole('option', { name: 'Profesorado' });

        await expect(lecturers).toHaveAttribute('aria-disabled', 'true');
        await expect(lecturers).not.toHaveAttribute('data-active', '');
        await expect(list.getByRole('option', { name: 'Secretaría' })).toHaveAttribute(
            'data-active',
            '',
        );

        await lecturers.click({ force: true });
        await expect(native).toHaveValue('');

        await audience.press('Enter');
        await expect(audience).toHaveValue('Secretaría');
        await expect(native).toHaveValue('secretaries');

        // Back to the empty value, which is a real option here
        await choose(audience, 'Todos');
        await expect(native).toHaveValue('');
    });

    test('el error y el estado deshabilitado llegan al combobox', async ({ ui }) => {
        const section = ui.section('field');
        const target = field(section, 'Comité de destino');
        const locked = field(section, 'Comité asignado');

        await expect(target).toHaveAttribute('aria-invalid', 'true');
        await expect(target).toHaveAccessibleDescription('Elige un comité de destino');
        await expect(locked).toBeDisabled();
        await expect(locked).toHaveValue('Comunicación');

        await locked.click({ force: true });
        await expect(locked).toHaveAttribute('aria-expanded', 'false');
    });

    test('un cambio hecho en el select desde fuera se refleja', async ({ ui }) => {
        const section = ui.section('field');
        const committee = field(section, 'Comité');

        await section.locator('select#field-committee-id').selectOption('4');

        await expect(committee).toHaveValue('Patrocinio');
    });

    test('la sección con una lista abierta cumple WCAG 2.2 AA en claro y oscuro', async ({
        ui,
    }) => {
        for (const theme of ['light', 'dark']) {
            await ui.setTheme(theme);

            const province = field(ui.section('field'), 'Provincia');

            await province.click();
            await expect(await listbox(province)).toBeVisible();

            expect(await ui.axe('#ui-field'), `Tema ${theme}`).toEqual([]);
        }
    });

    test('en móvil las opciones miden 44 px y nada desborda', async ({ page, ui }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'Los 44 px se exigen con puntero grueso');

        const section = ui.section('field');
        const province = field(section, 'Provincia');
        const list = await listbox(province);
        const box = await province.locator('xpath=..').boundingBox();

        expect(box.height).toBeGreaterThanOrEqual(44);

        await province.tap();
        await expect(list).toBeVisible();

        const options = list.getByRole('option');

        for (let i = 0; i < 5; i++) {
            const option = await options.nth(i).boundingBox();

            expect(option.height, `Opción ${i + 1}`).toBeGreaterThanOrEqual(44);
        }

        const popup = await list.locator('xpath=..').boundingBox();
        const viewport = page.viewportSize();

        expect(popup.x).toBeGreaterThanOrEqual(0);
        expect(popup.x + popup.width).toBeLessThanOrEqual(viewport.width);
        await ui.noOverflow();

        await list.getByRole('option', { name: 'Almería' }).tap();
        await expect(province).toHaveValue('Almería');
        await expect(section.locator('select#field-province')).toHaveValue('Almería');
    });
});

test.describe('Desplegables en un formulario', () => {
    test('el formulario envía el valor elegido', async ({ page }) => {
        await page.goto('/_ui#ui-filter-bar');

        const bar = page.locator('#activity-filters');
        const committee = bar.getByRole('combobox', { name: 'Comité', exact: true });

        await choose(committee, 'Logística');
        await bar.getByRole('button', { name: 'Aplicar' }).click();

        await expect(page).toHaveURL(/[?&]filter_committee=2(&|$)/);
        await expect(page).toHaveURL(/[?&]activity_type=evidence(&|$)/);
    });
});

test.describe('Desplegables sin JavaScript', () => {
    test.use({ javaScriptEnabled: false });

    test('el select nativo es el control y la etiqueta apunta a él', async ({ page }) => {
        await page.goto('/_ui#ui-field');

        const section = page.locator('#ui-field');
        const native = section.getByRole('combobox', { name: 'Estado', exact: true });

        await expect(native).toBeVisible();
        await expect(native).toHaveJSProperty('tagName', 'SELECT');
        await expect(section.locator('#field-session-state-combobox')).toBeHidden();

        await native.selectOption('closed');
        await expect(native).toHaveValue('closed');
    });
});
