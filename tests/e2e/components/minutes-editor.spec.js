import { expect, test } from '../fixtures/ui.js';

test.describe('Editor de actas', () => {
    let form;
    let editor;
    let points;
    let hidden;
    let output;

    const value = async () => JSON.parse(await hidden.inputValue());
    // The remove buttons repeat the field names in their labels, so the fields are found by role
    const title = (fieldset) => fieldset.getByRole('textbox', { name: 'Título', exact: true });
    const agreement = (fieldset, j, i) =>
        fieldset.getByRole('textbox', { name: `Acuerdo ${j} del punto ${i}`, exact: true });

    test.beforeEach(async ({ page, ui }) => {
        await page.goto('/_ui#ui-minutes-editor');
        form = ui.section('minutes-editor').locator('form');
        editor = form.getByRole('group', { name: 'Puntos del acta' });
        points = editor.locator('fieldset[data-point]');
        hidden = editor.locator('input[type="hidden"]');
        output = editor.locator('output');

        await expect(points).toHaveCount(3);
    });

    test('añadir un punto enfoca su título y los acuerdos se añaden y se quitan', async () => {
        await editor.getByRole('button', { name: 'Añadir punto' }).click();

        await expect(points).toHaveCount(4);

        const fourth = points.nth(3);

        await expect(title(fourth)).toBeFocused();
        await expect(editor.locator('p[role="status"]')).toHaveText('Punto 4 añadido');
        await title(fourth).fill('Ruegos y preguntas');
        await expect(
            editor.getByRole('group', { name: 'Punto 4, Ruegos y preguntas' }),
        ).toBeVisible();

        await fourth.getByRole('button', { name: 'Añadir acuerdo' }).click();

        await expect(agreement(fourth, 1, 4)).toBeFocused();
        await agreement(fourth, 1, 4).fill('Convocar la próxima reunión en noviembre.');
        await expect.poll(value).toHaveLength(4);
        expect((await value())[3].agreements).toEqual([
            { id: null, description: 'Convocar la próxima reunión en noviembre.' },
        ]);

        await fourth.getByRole('button', { name: 'Quitar el acuerdo 1 del punto 4' }).click();

        await expect(agreement(fourth, 1, 4)).toHaveCount(0);
        await expect(fourth.getByRole('button', { name: 'Añadir acuerdo' })).toBeFocused();
    });

    test('el envío con un título vacío se bloquea, enfoca el campo y lo anuncia', async ({
        page,
    }) => {
        const third = points.nth(2);
        const save = form.getByRole('button', { name: 'Guardar el acta' });

        await save.click();

        await expect(page).not.toHaveURL(/points_json=/);
        await expect(title(third)).toBeFocused();
        await expect(title(third)).toHaveAttribute('aria-invalid', 'true');
        await expect(third.getByText('El título es obligatorio')).toBeVisible();
        await expect(title(third)).toHaveAccessibleDescription('El título es obligatorio');
        // One announcement: the status, plus the description of the field that takes the focus
        await expect(editor.locator('p[role="status"]')).toHaveText('Revisa 1 punto');
        await expect(editor.getByRole('alert')).toHaveCount(0);
        await expect(title(points.nth(0))).not.toHaveAttribute('aria-invalid', 'true');

        // The editor cancels the submit before the busy state of x-ui.form: nothing stays locked
        await expect(form).toHaveAttribute('aria-busy', 'false');
        await expect(save).toBeEnabled();
        await expect(save).toHaveText('Guardar el acta');
        await expect(save).not.toHaveAttribute('aria-busy', 'true');

        // A second try says it again: the repeated text changes the region with a no-break space
        await save.click();
        await expect
            .poll(() => editor.locator('p[role="status"]').evaluate((region) => region.textContent))
            .toBe('Revisa 1 punto\u00A0');
        await expect(page).not.toHaveURL(/points_json=/);

        // Typing a title clears the error and the second submit goes through
        await title(third).fill('Cierre');
        await expect(title(third)).not.toHaveAttribute('aria-invalid', 'true');
        await expect(third.getByText('El título es obligatorio')).toBeHidden();

        await save.click();
        await expect(page).toHaveURL(/points_json=/);
    });

    test('el JSON conserva los ids, descarta los acuerdos vacíos y sigue el orden', async () => {
        const initial = await value();

        expect(initial.map((point) => point.id)).toEqual([41, 42, null]);
        expect(initial[0].agreements[0].id).toBe(7);
        expect(initial[1].agreements.map((item) => item.id)).toEqual([8, null]);
        expect(initial[1].duration).toBe(25);
        await expect(output).toHaveText(JSON.stringify(initial));

        const second = points.nth(1);

        await second.getByRole('button', { name: 'Añadir acuerdo' }).click();
        await expect(agreement(second, 3, 2)).toBeFocused();
        expect((await value())[1].agreements).toHaveLength(2);

        await second.getByRole('spinbutton', { name: 'Duración (min)' }).fill('30');
        await expect.poll(async () => (await value())[1].duration).toBe(30);

        await second.getByRole('spinbutton', { name: 'Duración (min)' }).fill('');
        await expect.poll(async () => (await value())[1].duration).toBeNull();

        await second.getByRole('button', { name: 'Subir el punto 2 de 3' }).click();

        await expect
            .poll(async () => (await value()).map((point) => point.id))
            .toEqual([42, 41, null]);
        await expect(editor.getByRole('button', { name: 'Subir el punto 1 de 3' })).toBeDisabled();
        await expect(editor.getByRole('button', { name: 'Bajar el punto 1 de 3' })).toBeFocused();
        await expect(editor.locator('p[role="status"]')).toHaveText(
            'Punto 2 movido a la posición 1',
        );
        await expect(title(points.nth(0))).toHaveValue('Presupuesto del photocall');
    });

    test('quitar un punto pide confirmación y gestiona el foco', async ({ page }) => {
        const remove = editor.getByRole('button', { name: 'Quitar el punto 3 de 3' });
        const confirm = editor.getByRole('button', { name: 'Confirmar que quitas el punto 3' });

        await remove.click();

        await expect(confirm).toBeVisible();
        await expect(confirm).toBeFocused();
        await expect(remove).toBeHidden();

        // The confirmation lasts until Escape (focus back on «Quitar») or until the focus leaves it
        await page.keyboard.press('Escape');
        await expect(remove).toBeFocused();
        await expect(confirm).toBeHidden();

        await remove.click();
        await expect(confirm).toBeFocused();
        await page.keyboard.press('Shift+Tab');
        await expect(confirm).toBeHidden();
        await expect(remove).toBeVisible();
        await expect(points).toHaveCount(3);

        await remove.click();
        await confirm.click();

        await expect(points).toHaveCount(2);
        await expect.poll(async () => (await value()).map((point) => point.id)).toEqual([41, 42]);
        await expect(editor.getByRole('button', { name: 'Añadir punto' })).toBeFocused();

        await editor.getByRole('button', { name: 'Quitar el punto 1 de 2' }).click();
        await editor.getByRole('button', { name: 'Confirmar que quitas el punto 1' }).click();

        await expect(points).toHaveCount(1);
        await expect(editor.getByRole('button', { name: 'Quitar el punto 1 de 1' })).toBeFocused();
    });

    test('la confirmación de quitar un punto no caduca con el tiempo', async ({ page }) => {
        await page.clock.install();
        await page.goto('/_ui#ui-minutes-editor');

        const confirm = editor.getByRole('button', { name: 'Confirmar que quitas el punto 1' });

        await editor.getByRole('button', { name: 'Quitar el punto 1 de 3' }).click();
        await expect(confirm).toBeFocused();

        await page.clock.runFor(30_000);

        await expect(confirm).toBeVisible();
        await expect(confirm).toBeFocused();
    });

    test('en móvil los controles miden al menos 44 px y nada desborda', async ({
        page,
        ui,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'Los 44 px se exigen con puntero grueso');

        expect(await page.evaluate(() => matchMedia('(pointer: coarse)').matches)).toBe(true);

        const buttons = form.locator('button:visible');
        const fields = editor.locator('input:visible, textarea:visible');

        for (let i = 0; i < (await buttons.count()); i++) {
            const box = await buttons.nth(i).boundingBox();

            expect(box).not.toBeNull();
            expect(box.height, `Botón ${i + 1}`).toBeGreaterThanOrEqual(44);
            expect(box.width, `Botón ${i + 1}`).toBeGreaterThanOrEqual(44);
        }

        for (let i = 0; i < (await fields.count()); i++) {
            const box = await fields.nth(i).boundingBox();

            expect(box).not.toBeNull();
            expect(box.height, `Campo ${i + 1}`).toBeGreaterThanOrEqual(44);
        }

        await ui.noOverflow();
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-minutes-editor')).toEqual([]);
    });
});
