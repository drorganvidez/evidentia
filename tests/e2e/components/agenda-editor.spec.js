import { expect, test } from '../fixtures/ui.js';

const INITIAL = [
    'Lectura y aprobación del acta anterior',
    'Presupuesto del photocall',
    'Calendario de montaje',
];

/**
 * WebKit on macOS and iOS does not focus a clicked button: the mousedown hands the focus to the closest ancestor
 * that takes it (the editor, with tabindex -1), without scrolling, or to nothing, and only then the click arrives.
 * Chromium focuses the button, so this capture listener reproduces the WebKit rule for the rest of the test.
 */
const emulateSafariButtonFocus = (page) =>
    page.evaluate(() => {
        document.addEventListener(
            'mousedown',
            (event) => {
                const button =
                    event.target instanceof Element ? event.target.closest('button') : null;

                if (!button) {
                    return;
                }

                event.preventDefault();

                const holder = button.parentElement?.closest('[tabindex]');

                if (holder) {
                    holder.focus({ preventScroll: true });
                } else {
                    document.activeElement?.blur();
                }
            },
            true,
        );
    });

test.describe('Orden del día', () => {
    let editor;
    let hidden;
    let output;

    // The buttons are named «Subir el punto N de M», so the inputs are found by role and exact name
    const point = (n) => editor.getByRole('textbox', { name: `Punto ${n}`, exact: true });

    test.beforeEach(async ({ page, ui }) => {
        await page.goto('/_ui#ui-agenda-editor');
        editor = ui.section('agenda-editor').locator('[data-demo="filled"]');
        hidden = editor.locator('input[type="hidden"]');
        output = editor.locator('output');

        await expect(hidden).toHaveValue(JSON.stringify(INITIAL));
    });

    test('añadir con Intro enfoca el nuevo punto y el campo oculto lo refleja', async ({
        page,
    }) => {
        const draft = editor.getByRole('textbox', { name: 'Nuevo punto' });
        const rows = editor.getByRole('listitem');

        await expect(rows).toHaveCount(3);

        await draft.fill('Ruegos y preguntas');
        await draft.press('Enter');

        await expect(rows).toHaveCount(4);
        await expect(point(4)).toBeFocused();
        await expect(point(4)).toHaveValue('Ruegos y preguntas');
        await expect(draft).toHaveValue('');
        await expect(hidden).toHaveValue(JSON.stringify([...INITIAL, 'Ruegos y preguntas']));
        await expect(output).toHaveText(JSON.stringify([...INITIAL, 'Ruegos y preguntas']));
        await expect(editor.locator('p[role="status"]')).toHaveText('Punto 4 añadido');

        // Enter inside a point opens an empty one below it; Backspace on the empty one removes it
        await page.keyboard.press('Enter');
        await expect(rows).toHaveCount(5);
        await expect(point(5)).toBeFocused();
        await expect(hidden).toHaveValue(JSON.stringify([...INITIAL, 'Ruegos y preguntas']));

        await page.keyboard.press('Backspace');
        await expect(rows).toHaveCount(4);
        await expect(point(4)).toBeFocused();

        // The add button ignores an empty draft and sends the focus back to it
        await editor.getByRole('button', { name: 'Añadir', exact: true }).click();
        await expect(rows).toHaveCount(4);
        await expect(draft).toBeFocused();
    });

    test('los botones y Alt con las flechas mueven los puntos y conservan el foco', async ({
        page,
    }) => {
        await editor.getByRole('button', { name: 'Subir el punto 2 de 3' }).click();

        await expect(hidden).toHaveValue(JSON.stringify([INITIAL[1], INITIAL[0], INITIAL[2]]));
        await expect(editor.locator('p[role="status"]')).toHaveText(
            'Punto 2 movido a la posición 1',
        );
        // At the top «Subir» is disabled, so the focus lands on «Bajar» of the same point
        await expect(editor.getByRole('button', { name: 'Subir el punto 1 de 3' })).toBeDisabled();
        await expect(editor.getByRole('button', { name: 'Bajar el punto 1 de 3' })).toBeFocused();

        await editor.getByRole('button', { name: 'Bajar el punto 1 de 3' }).click();
        await expect(hidden).toHaveValue(JSON.stringify(INITIAL));
        await expect(editor.getByRole('button', { name: 'Bajar el punto 2 de 3' })).toBeFocused();

        await point(1).focus();
        await page.keyboard.press('Alt+ArrowDown');

        await expect(hidden).toHaveValue(JSON.stringify([INITIAL[1], INITIAL[0], INITIAL[2]]));
        await expect(point(2)).toBeFocused();
        await expect(point(2)).toHaveValue(INITIAL[0]);

        await page.keyboard.press('Alt+ArrowUp');
        await expect(hidden).toHaveValue(JSON.stringify(INITIAL));
        await expect(point(1)).toBeFocused();
    });

    test('quitar pide confirmación, que dura hasta Escape, salir del botón o confirmar', async ({
        page,
    }) => {
        const rows = editor.getByRole('listitem');
        const remove = editor.getByRole('button', { name: 'Quitar el punto 2 de 3' });
        const confirm = editor.getByRole('button', { name: 'Confirmar que quitas el punto 2' });

        await remove.click();

        await expect(confirm).toBeVisible();
        await expect(confirm).toBeFocused();
        await expect(remove).toBeHidden();
        await expect(rows).toHaveCount(3);
        await expect(editor.locator('p[role="status"]')).toHaveText(
            'Pulsa Confirmar para quitar el punto 2',
        );

        // Escape brings «Quitar» back with the focus
        await page.keyboard.press('Escape');
        await expect(remove).toBeVisible();
        await expect(remove).toBeFocused();
        await expect(confirm).toBeHidden();

        // Leaving the button brings it back too, without pulling the focus
        await remove.click();
        await expect(confirm).toBeFocused();
        await page.keyboard.press('Tab');
        await expect(confirm).toBeHidden();
        await expect(remove).toBeVisible();
        await expect(remove).not.toBeFocused();
        await expect(hidden).toHaveValue(JSON.stringify(INITIAL));

        await remove.click();
        await confirm.click();

        await expect(rows).toHaveCount(2);
        await expect(hidden).toHaveValue(JSON.stringify([INITIAL[0], INITIAL[2]]));
        await expect(editor.getByRole('button', { name: 'Quitar el punto 2 de 2' })).toBeFocused();

        // Removing the last point sends the focus to the new point field
        await editor.getByRole('button', { name: 'Quitar el punto 2 de 2' }).click();
        await editor.getByRole('button', { name: 'Confirmar que quitas el punto 2' }).click();

        await expect(rows).toHaveCount(1);
        await expect(editor.getByRole('textbox', { name: 'Nuevo punto' })).toBeFocused();
        await expect(hidden).toHaveValue(JSON.stringify([INITIAL[0]]));
    });

    test('confirmar quita el punto aunque el clic no enfoque el botón, como en Safari', async ({
        page,
    }) => {
        await emulateSafariButtonFocus(page);

        const rows = editor.getByRole('listitem');

        await editor.getByRole('button', { name: 'Quitar el punto 2 de 3' }).click();
        await editor.getByRole('button', { name: 'Confirmar que quitas el punto 2' }).click();

        await expect(rows).toHaveCount(2);
        await expect(hidden).toHaveValue(JSON.stringify([INITIAL[0], INITIAL[2]]));
        await expect(editor.getByRole('button', { name: /^Confirmar/ })).toHaveCount(0);

        // Clicking another control, which does take the focus, still takes the confirmation back
        await editor.getByRole('button', { name: 'Quitar el punto 1 de 2' }).click();
        await expect(
            editor.getByRole('button', { name: 'Confirmar que quitas el punto 1' }),
        ).toBeFocused();
        await point(2).click();
        await expect(editor.getByRole('button', { name: /^Confirmar/ })).toHaveCount(0);
        await expect(rows).toHaveCount(2);
    });

    test('la confirmación de quitar no caduca con el tiempo', async ({ page }) => {
        await page.clock.install();
        await page.goto('/_ui#ui-agenda-editor');

        const confirm = editor.getByRole('button', { name: 'Confirmar que quitas el punto 2' });

        await editor.getByRole('button', { name: 'Quitar el punto 2 de 3' }).click();
        await expect(confirm).toBeFocused();

        // WCAG 2.2.1: no time limit, the person decides
        await page.clock.runFor(30_000);

        await expect(confirm).toBeVisible();
        await expect(confirm).toBeFocused();
    });

    test('el máximo de puntos se respeta y se anuncia', async ({ ui }) => {
        const small = ui.section('agenda-editor').locator('[data-demo="empty"]');
        const draft = small.getByRole('textbox', { name: 'Nuevo punto' });

        for (const text of ['Uno', 'Dos', 'Tres', 'Cuatro']) {
            await draft.fill(text);
            await draft.press('Enter');
        }

        await expect(small.getByRole('listitem')).toHaveCount(3);
        await expect(small.locator('p[role="status"]')).toHaveText(
            'No caben más puntos, el máximo es 3',
        );
        await expect(small.locator('input[type="hidden"]')).toHaveValue(
            JSON.stringify(['Uno', 'Dos', 'Tres']),
        );

        // The same message again still changes the region (a trailing no-break space), so it is read again
        const text = () =>
            small.locator('p[role="status"]').evaluate((region) => region.textContent);

        await expect.poll(text).toBe('No caben más puntos, el máximo es 3');
        await draft.press('Enter');
        await expect.poll(text).toBe('No caben más puntos, el máximo es 3\u00A0');
        await draft.press('Enter');
        await expect.poll(text).toBe('No caben más puntos, el máximo es 3');
    });

    test('en móvil los controles miden al menos 44 px y nada desborda', async ({
        page,
        ui,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'Los 44 px se exigen con puntero grueso');

        expect(await page.evaluate(() => matchMedia('(pointer: coarse)').matches)).toBe(true);

        const buttons = editor.locator('button:visible');
        const inputs = editor.locator('input[type="text"]');

        for (let i = 0; i < (await buttons.count()); i++) {
            const box = await buttons.nth(i).boundingBox();

            expect(box).not.toBeNull();
            expect(box.height, `Botón ${i + 1}`).toBeGreaterThanOrEqual(44);
            expect(box.width, `Botón ${i + 1}`).toBeGreaterThanOrEqual(44);
        }

        for (let i = 0; i < (await inputs.count()); i++) {
            const box = await inputs.nth(i).boundingBox();

            expect(box).not.toBeNull();
            expect(box.height, `Campo ${i + 1}`).toBeGreaterThanOrEqual(44);
        }

        await ui.noOverflow();
    });

    test('la sección cumple WCAG 2.2 AA', async ({ ui }) => {
        expect(await ui.axe('#ui-agenda-editor')).toEqual([]);
    });
});
