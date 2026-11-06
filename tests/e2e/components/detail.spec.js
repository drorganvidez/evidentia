import { expect, test } from '../fixtures/ui.js';

/** Waits for the finite animations (entrances); the aurora's infinite ones are left alone. */
async function settle(page) {
    await page.evaluate(() =>
        Promise.race([
            Promise.all(
                document
                    .getAnimations()
                    .filter((animation) => animation.effect.getTiming().iterations !== Infinity)
                    .map((animation) => animation.finished),
            ),
            new Promise((resolve) => setTimeout(resolve, 3000)),
        ]),
    );
}

test.describe('Detalle de una evidencia', () => {
    test('compone la pantalla completa con el shell y sin errores', async ({ page, ui }) => {
        const response = await page.goto('/_ui/detail');

        expect(response.status()).toBe(200);

        await expect(
            page.getByRole('heading', { level: 1, name: 'Montaje del photocall' }),
        ).toBeVisible();
        await expect(page.getByRole('main')).toHaveAttribute('aria-labelledby', 'page-title');
        await expect(page.getByRole('list', { name: 'Recorrido de la evidencia' })).toBeVisible();
        await expect(
            page.getByRole('note').filter({ hasText: 'Tu coordinador necesita más pruebas' }),
        ).toBeVisible();
        await expect(
            page.getByRole('list', { name: 'Pruebas de la evidencia' }).getByRole('listitem'),
        ).toHaveCount(3);
        await expect(page.getByRole('list', { name: 'Historial de la evidencia' })).toBeAttached();
        await expect(page.getByText('EV-0033').first()).toBeVisible();
        await expect(page.locator('[x-data="upload"]')).toBeAttached();
        await ui.noOverflow();
    });

    test('las acciones de edición llevan el icono pen-line del catálogo', async ({ page }) => {
        await page.goto('/_ui/detail');

        const edits = page.getByRole('link', { name: 'Corregir y volver a enviar' });

        await expect(edits).toHaveCount(2);

        for (const edit of await edits.all()) {
            // pen-line draws the base line; pencil draws the ferrule instead
            await expect(edit.locator('svg path[d="M13 21h8"]')).toHaveCount(1);
            await expect(edit.locator('svg path[d="m15 5 4 4"]')).toHaveCount(0);
        }
    });

    test('la eliminación pide confirmación en un diálogo de alerta', async ({ page }) => {
        await page.goto('/_ui/detail');

        const trigger = page.getByRole('button', { name: 'Eliminar', exact: true });
        await trigger.click();

        const dialog = page.getByRole('alertdialog', { name: 'Eliminar la evidencia' });
        await expect(dialog).toBeVisible();
        await expect(dialog.getByRole('button', { name: 'Cancelar' })).toBeFocused();
        await expect(dialog.getByRole('button', { name: 'Sí, eliminar' })).toBeVisible();

        await page.keyboard.press('Escape');
        await expect(dialog).toBeHidden();
        await expect(trigger).toBeFocused();
    });

    test('confirmar la eliminación envía un DELETE con token y vuelve con un aviso', async ({
        page,
    }) => {
        await page.goto('/_ui/detail');

        // Every form that changes data posts to the demo sink, as the real screens will post to theirs
        await expect(page.locator('form[action$="/_ui/detail"]')).toHaveCount(0);
        await expect(page.locator('form[method="get"]:not([role="search"])')).toHaveCount(0);
        await expect(page.locator('form[method="post"][action$="/_ui/demo/submit"]')).toHaveCount(
            4,
        );
        await expect(page.locator('#proof-form')).toHaveAttribute('action', /\/_ui\/demo\/submit$/);

        await page.getByRole('button', { name: 'Eliminar', exact: true }).click();

        const dialog = page.getByRole('alertdialog', { name: 'Eliminar la evidencia' });
        const sent = page.waitForRequest((request) => request.url().endsWith('/_ui/demo/submit'));

        await dialog.getByRole('button', { name: 'Sí, eliminar' }).click();

        const request = await sent;
        const fields = new URLSearchParams(request.postData());

        expect(request.method()).toBe('POST');
        expect(fields.get('_method')).toBe('DELETE');
        expect(fields.get('_token')).toBeTruthy();

        await page.waitForURL(/\/_ui\/detail$/);
        await expect(
            page
                .getByRole('region', { name: 'Avisos' })
                .getByRole('group')
                .filter({ hasText: 'Formulario recibido' }),
        ).toBeVisible();
    });

    test('en el teléfono apila el lateral bajo el contenido y muestra la barra inferior', async ({
        page,
        ui,
        isMobile,
    }) => {
        test.skip(!isMobile, 'Solo en el proyecto móvil');

        await page.goto('/_ui/detail');

        await expect(
            page.getByRole('navigation', { name: 'Navegación principal (móvil)' }),
        ).toBeVisible();
        await expect(page.getByRole('button', { name: 'Abrir menú' })).toBeVisible();

        const proofs = await page
            .getByRole('heading', { name: 'Pruebas', exact: true })
            .boundingBox();
        const data = await page.getByRole('heading', { name: 'Datos', exact: true }).boundingBox();

        expect(data.y, 'El lateral va debajo de las pruebas').toBeGreaterThan(proofs.y);
        await ui.noOverflow();
    });

    for (const width of [1024, 1100]) {
        test(`a ${width} px los valores de la tarjeta Datos no se salen de ella`, async ({
            page,
            ui,
        }, testInfo) => {
            test.skip(
                testInfo.project.name !== 'chromium',
                'Las dos columnas del detalle se comprueban en escritorio',
            );

            await page.setViewportSize({ width, height: 900 });
            await page.goto('/_ui/detail');

            const card = page.getByRole('region', { name: 'Datos', exact: true });
            const aside = page.getByRole('complementary', { name: 'Datos e historial' });
            const proofs = page.getByRole('region', { name: 'Pruebas', exact: true });

            const asideBox = await aside.boundingBox();

            expect(asideBox.x, 'El lateral va a la derecha').toBeGreaterThan(
                (await proofs.boundingBox()).x,
            );
            // The aside keeps a floor of 18rem, so the pairs of the card stay side by side
            expect(asideBox.width, 'Anchura del lateral').toBeGreaterThanOrEqual(288);
            await expect(card.locator('dl > div').first()).toHaveCSS(
                'grid-template-columns',
                /^\d+(\.\d+)?px \d+(\.\d+)?px$/,
            );

            const outside = await card.evaluate((section) => {
                const bounds = section.getBoundingClientRect();
                const list = section.querySelector('dl').getBoundingClientRect();

                return Array.from(section.querySelectorAll('dd, dd *'))
                    .map((el) => ({ el, box: el.getBoundingClientRect() }))
                    .filter(({ box }) => box.width > 1 && box.height > 1)
                    .filter(
                        ({ box }) =>
                            box.left < list.left - 0.5 ||
                            box.right > list.right + 0.5 ||
                            box.right > bounds.right,
                    )
                    .map(
                        ({ el, box }) =>
                            `${el.textContent.replace(/\s+/g, ' ').trim()} (${Math.round(box.right)})`,
                    );
            });

            expect(outside, 'Valores fuera de la lista').toEqual([]);
            await ui.noOverflow();
        });
    }

    for (const theme of ['light', 'dark']) {
        test(`cumple WCAG 2.2 AA en tema ${theme === 'dark' ? 'oscuro' : 'claro'}`, async ({
            page,
            ui,
        }) => {
            await page.goto('/_ui/detail');
            await ui.setTheme(theme);
            await expect(
                page.getByRole('heading', { level: 1, name: 'Montaje del photocall' }),
            ).toBeVisible();
            await settle(page);

            const violations = await ui.axe();

            expect(violations, JSON.stringify(violations, null, 2)).toEqual([]);
        });
    }
});
