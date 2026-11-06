import { expect, test } from '../fixtures/ui.js';

/** No horizontal overflow inside a document (the page or a frame). */
async function noOverflowIn(root, where = '') {
    const widths = await root.locator('html').evaluate((el) => ({
        scroll: el.scrollWidth,
        client: el.clientWidth,
    }));

    expect(widths.scroll, `Desborde horizontal${where}`).toBeLessThanOrEqual(widths.client);
}

/** A frame receives its document when it scrolls into view: bring one there and return its locator. */
async function shellFrame(page, width) {
    const iframe = page.locator(`iframe[data-demo="shell-${width}"]`);

    await iframe.scrollIntoViewIfNeeded();

    return page.frameLocator(`iframe[data-demo="shell-${width}"]`);
}

test.describe('Shell en la sección layout', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-layout');
    });

    test('a 1280 px muestra el sidebar completo y oculta la barra inferior', async ({ page }) => {
        const frame = await shellFrame(page, 1280);

        await expect(frame.getByRole('heading', { level: 1, name: 'Hola, Juan' })).toBeVisible();
        await expect(frame.locator('aside')).toBeVisible();
        await expect(
            frame.locator('aside').getByRole('link', { name: 'Mis evidencias' }),
        ).toBeVisible();
        await expect(frame.locator('aside').getByText('Gestión')).toBeVisible();
        await expect(frame.locator('aside [x-data="deadline"]')).toBeVisible();
        await expect(
            frame.getByRole('navigation', { name: 'Navegación principal (móvil)' }),
        ).toBeHidden();
        await expect(frame.getByRole('button', { name: 'Abrir menú' })).toBeHidden();
        await expect(frame.getByRole('search')).toBeVisible();

        const aside = await frame.locator('aside').boundingBox();
        expect(Math.round(aside.width)).toBe(272);
        await noOverflowIn(frame);
    });

    test('a 768 px el sidebar se compacta en un raíl de 80 px', async ({ page }) => {
        const frame = await shellFrame(page, 768);

        await expect(frame.locator('aside')).toBeVisible();

        const aside = await frame.locator('aside').boundingBox();
        expect(Math.round(aside.width)).toBe(80);

        await expect(frame.locator('aside [x-data="deadline"]')).toBeHidden();
        await expect(
            frame.getByRole('navigation', { name: 'Navegación principal (móvil)' }),
        ).toBeHidden();
        await expect(frame.getByRole('button', { name: 'Abrir menú' })).toBeHidden();
        // The deadline comes back inline under the title
        await expect(frame.locator('main [x-data="deadline"]')).toBeVisible();
        await noOverflowIn(frame);
    });

    test('a 400 px oculta el sidebar y muestra la barra inferior y el botón de menú', async ({
        page,
    }) => {
        const frame = await shellFrame(page, 400);

        await expect(frame.locator('aside')).toBeHidden();
        await expect(
            frame.getByRole('navigation', { name: 'Navegación principal (móvil)' }),
        ).toBeVisible();
        await expect(frame.getByRole('button', { name: 'Abrir menú' })).toBeVisible();
        await expect(frame.getByRole('search')).toBeHidden();
        await expect(frame.getByRole('link', { name: 'Avisos, 3 sin leer' })).toBeVisible();
        await noOverflowIn(frame);
    });
});

test.describe('Shell en su propia página', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui/shell');
    });

    test('el botón de menú abre el cajón, el foco queda dentro y Escape lo devuelve', async ({
        page,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'El cajón es cosa del teléfono');

        const button = page.getByRole('button', { name: 'Abrir menú' });
        const drawer = page.locator('#drawer');

        await expect(
            page.getByRole('navigation', { name: 'Navegación principal (móvil)' }),
        ).toBeVisible();
        await expect(page.locator('aside')).toBeHidden();
        await expect(button).toHaveAttribute('aria-expanded', 'false');

        await button.click();

        await expect(drawer).toHaveAttribute('open', '');
        await expect(button).toHaveAttribute('aria-expanded', 'true');
        expect(await page.evaluate(() => document.activeElement.closest('#drawer') !== null)).toBe(
            true,
        );
        await expect(drawer.getByRole('link', { name: 'Mis evidencias' })).toBeVisible();
        await expect(drawer.getByRole('radio', { name: 'Oscuro' })).toBeVisible();
        await expect(drawer.getByRole('button', { name: 'Salir' })).toBeVisible();
        await expect(drawer.locator('[x-data="deadline"]')).toBeVisible();

        await page.keyboard.press('Escape');

        await expect(drawer).not.toHaveAttribute('open', '');
        await expect(button).toHaveAttribute('aria-expanded', 'false');
        await expect(button).toBeFocused();
        await noOverflowIn(page);

        // The close button of the drawer resets it as well
        await button.click();
        await expect(button).toHaveAttribute('aria-expanded', 'true');
        await drawer.getByRole('button', { name: 'Cerrar' }).click();
        await expect(drawer).not.toHaveAttribute('open', '');
        await expect(button).toHaveAttribute('aria-expanded', 'false');
    });

    test('el enlace para saltar lleva el foco a main y la barra «/» al buscador', async ({
        page,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Teclado del escritorio');

        await page.keyboard.press('Tab');
        await expect(page.getByRole('link', { name: 'Saltar al contenido' })).toBeFocused();
        await page.keyboard.press('Enter');
        await expect(page.locator('#main')).toBeFocused();

        await page.keyboard.press('/');
        await expect(page.locator('#field-q-topbar')).toBeFocused();

        // Inside a field the key is typed, not captured
        await page.keyboard.type('acta/');
        await expect(page.locator('#field-q-topbar')).toHaveValue('acta/');
    });

    test('con los atajos apagados «/» no mueve el foco y la pista desaparece', async ({
        page,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Teclado del escritorio');

        const field = page.locator('#field-q-topbar');
        const hint = page.locator('header kbd');

        await expect(field).toHaveAttribute('aria-keyshortcuts', '/');
        await expect(hint).toBeVisible();

        await page.evaluate(() => localStorage.setItem('evidentia.shortcuts', 'off'));
        await page.reload();

        await expect(field).not.toHaveAttribute('aria-keyshortcuts');
        await expect(hint).toBeHidden();

        await page.locator('#main').focus();
        await page.keyboard.press('/');
        await expect(field).not.toBeFocused();
        await expect(page.locator('#main')).toBeFocused();

        await page.evaluate(() => localStorage.setItem('evidentia.shortcuts', 'on'));
        await page.reload();

        await page.locator('#main').focus();
        await page.keyboard.press('/');
        await expect(field).toBeFocused();
    });

    test('el menú de usuario apaga los atajos y el ajuste sigue tras recargar', async ({
        page,
    }, testInfo) => {
        test.skip(
            testInfo.project.name !== 'chromium',
            'El menú de usuario vive en la topbar ancha',
        );

        const trigger = page.getByRole('button', { name: 'Menú de Juan Pérez' });
        const item = page.locator('#user-menu').getByRole('menuitemcheckbox', {
            name: 'Atajos de teclado',
            includeHidden: true,
        });

        await trigger.click();
        await expect(item).toHaveAttribute('aria-checked', 'true');
        await item.click();
        await expect(item).toHaveAttribute('aria-checked', 'false');
        await page.keyboard.press('Escape');

        await page.locator('#main').focus();
        await page.keyboard.press('/');
        await expect(page.locator('#field-q-topbar')).not.toBeFocused();

        await page.reload();

        await expect(item).toHaveAttribute('aria-checked', 'false');
        await expect(page.locator('header kbd')).toBeHidden();
        await page.locator('#main').focus();
        await page.keyboard.press('/');
        await expect(page.locator('#field-q-topbar')).not.toBeFocused();

        // Back on from the same item
        await trigger.click();
        await item.click();
        await expect(item).toHaveAttribute('aria-checked', 'true');
        await page.keyboard.press('Escape');
        await page.locator('#main').focus();
        await page.keyboard.press('/');
        await expect(page.locator('#field-q-topbar')).toBeFocused();
    });

    test('el cajón apaga los atajos con un conmutador que sigue tras recargar', async ({
        page,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'mobile', 'El cajón es cosa del teléfono');

        const drawer = page.locator('#drawer');
        const toggle = drawer.getByRole('switch', { name: 'Atajos de teclado' });

        await page.getByRole('button', { name: 'Abrir menú' }).click();
        await expect(toggle).toBeChecked();
        await expect(drawer.locator('kbd')).toBeVisible();

        await toggle.click();

        await expect(toggle).not.toBeChecked();
        await expect(drawer.locator('kbd')).toBeHidden();
        expect(await page.evaluate(() => localStorage.getItem('evidentia.shortcuts'))).toBe('off');

        await page.reload();
        await page.getByRole('button', { name: 'Abrir menú' }).click();
        await expect(toggle).not.toBeChecked();
    });

    test('el anillo de foco de las migas cabe en su lista recortada', async ({
        page,
    }, testInfo) => {
        test.skip(
            testInfo.project.name !== 'chromium',
            'Las migas anteriores viven en la topbar ancha',
        );

        const link = page.locator('nav[aria-label="Migas de pan"] a').first();

        await page.keyboard.press('Shift');
        await link.focus();

        const room = await link.evaluate((a) => {
            const style = getComputedStyle(a);
            const reach = parseFloat(style.outlineWidth) + parseFloat(style.outlineOffset);
            const own = a.getBoundingClientRect();
            const list = a.closest('ol').getBoundingClientRect();

            return {
                reach,
                top: own.top - list.top,
                bottom: list.bottom - own.bottom,
                left: own.left - list.left,
            };
        });

        expect(room.reach).toBeGreaterThan(0);
        expect(room.top, 'Arriba').toBeGreaterThanOrEqual(room.reach);
        expect(room.bottom, 'Abajo').toBeGreaterThanOrEqual(room.reach);
        expect(room.left, 'Izquierda').toBeGreaterThanOrEqual(room.reach);
    });

    test('con puntero grueso los enlaces de las migas miden 44 px', async ({ page }, testInfo) => {
        test.skip(
            testInfo.project.name !== 'mobile',
            'Solo el proyecto móvil tiene puntero grueso',
        );

        expect(await page.evaluate(() => matchMedia('(pointer: coarse)').matches)).toBe(true);

        // A tablet in both orientations: the parent alone below lg, every previous item from lg
        for (const width of [820, 1180]) {
            await page.setViewportSize({ width, height: 1000 });
            await page.goto('/_ui/detail');

            const links = page.locator('nav[aria-label="Migas de pan"] a');

            await expect(links.first()).toBeAttached();

            const boxes = await links.evaluateAll((all) =>
                all
                    .filter((link) => link.getClientRects().length > 0)
                    .map((link) => {
                        const { width: w, height: h } = link.getBoundingClientRect();

                        return { text: link.textContent.trim(), w, h };
                    }),
            );

            expect(boxes.length, `Migas visibles a ${width} px`).toBeGreaterThan(0);

            for (const box of boxes) {
                expect(box.h, `Alto de «${box.text}» a ${width} px`).toBeGreaterThanOrEqual(44);
                expect(box.w, `Ancho de «${box.text}» a ${width} px`).toBeGreaterThanOrEqual(44);
            }
        }
    });

    test('«Salir» envía un POST con su token y vuelve con el aviso', async ({ page }, testInfo) => {
        const sent = page.waitForRequest((request) => request.url().endsWith('/_ui/demo/submit'));

        if (testInfo.project.name === 'mobile') {
            await page.getByRole('button', { name: 'Abrir menú' }).click();
            await page.locator('#drawer').getByRole('button', { name: 'Salir' }).click();
        } else {
            await page.getByRole('button', { name: 'Menú de Juan Pérez' }).click();
            await page.locator('#user-menu').getByRole('menuitem', { name: 'Salir' }).click();
        }

        const request = await sent;

        expect(request.method()).toBe('POST');
        expect(new URLSearchParams(request.postData() ?? '').get('_token')).toBeTruthy();
        await expect(page).toHaveURL(/\/_ui\/shell$/);
        await expect(
            page.getByRole('region', { name: 'Avisos' }).getByText('Formulario recibido'),
        ).toBeVisible();
    });

    test('el menú de usuario abre desde el avatar con el nombre y Salir', async ({
        page,
    }, testInfo) => {
        test.skip(
            testInfo.project.name !== 'chromium',
            'El menú de usuario vive en la topbar ancha',
        );

        await page.getByRole('button', { name: 'Menú de Juan Pérez' }).click();

        const menu = page.locator('#user-menu');

        await expect(menu).toBeVisible();
        await expect(menu.getByText('Estudiante · Coordinador · Presidente')).toBeVisible();
        await expect(menu.getByRole('menuitem', { name: 'Salir' })).toBeVisible();

        await page.keyboard.press('Escape');
        await expect(menu).toBeHidden();
    });

    test('cumple WCAG 2.2 AA en claro y en oscuro sin desbordar', async ({ page, ui }) => {
        expect(await ui.axe()).toEqual([]);
        await noOverflowIn(page);

        await ui.setTheme('dark');
        await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
        expect(await ui.axe()).toEqual([]);
        await noOverflowIn(page);
    });

    test('en una ventana de 768 y de 400 px no desborda', async ({ page }, testInfo) => {
        test.skip(
            testInfo.project.name !== 'chromium',
            'Los tramos se fuerzan con el tamaño de la ventana',
        );

        await page.setViewportSize({ width: 768, height: 1024 });
        await expect(page.locator('aside')).toBeVisible();
        expect(Math.round((await page.locator('aside').boundingBox()).width)).toBe(80);
        await noOverflowIn(page);

        await page.setViewportSize({ width: 400, height: 800 });
        await expect(page.locator('aside')).toBeHidden();
        await expect(
            page.getByRole('navigation', { name: 'Navegación principal (móvil)' }),
        ).toBeVisible();
        await noOverflowIn(page);
    });
});

// Between md and xl the column is narrow: the shell (short breadcrumbs) and the detail (a long current one)
for (const path of ['/_ui/shell', '/_ui/detail']) {
    test.describe(`Topbar de ${path} en tableta y portátil pequeño`, () => {
        test.beforeEach(async ({ page }, testInfo) => {
            test.skip(
                testInfo.project.name !== 'chromium',
                'Los tramos se fuerzan con el tamaño de la ventana',
            );
            await page.goto(path);
        });

        test('la topbar cabe en la columna a 768, 1024 y 1100 px', async ({ page }) => {
            for (const width of [768, 1024, 1100]) {
                await page.setViewportSize({ width, height: 900 });

                const box = await page.evaluate(() => {
                    const right = (selector) =>
                        document.querySelector(selector).getBoundingClientRect().right;
                    const header = document.querySelector('header.glass');

                    return {
                        client: document.documentElement.clientWidth,
                        header: right('header.glass'),
                        main: right('main'),
                        footer: right('footer'),
                        inner: header.scrollWidth - header.clientWidth,
                    };
                });

                expect(box.header, `Topbar dentro de la ventana a ${width} px`).toBeLessThanOrEqual(
                    box.client,
                );
                expect(box.main, `Main dentro de la ventana a ${width} px`).toBeLessThanOrEqual(
                    box.client,
                );
                expect(box.footer, `Pie dentro de la ventana a ${width} px`).toBeLessThanOrEqual(
                    box.client,
                );
                expect(box.inner, `Nada se sale de la topbar a ${width} px`).toBeLessThanOrEqual(0);
            }
        });

        test('la miga actual se lee a 768, 1024 y 1100 px', async ({ page }) => {
            const current = page.locator('nav[aria-label="Migas de pan"] [aria-current="page"]');

            for (const width of [768, 1024, 1100]) {
                await page.setViewportSize({ width, height: 900 });

                const box = await current.boundingBox();

                expect(
                    box?.width ?? 0,
                    `Ancho de la miga actual a ${width} px`,
                ).toBeGreaterThanOrEqual(63.5);
            }
        });

        test('los enlaces de las migas se leen enteros a 768, 1024 y 1100 px', async ({ page }) => {
            for (const width of [768, 1024, 1100]) {
                await page.setViewportSize({ width, height: 900 });

                const cut = await page
                    .locator('nav[aria-label="Migas de pan"] a span')
                    .evaluateAll((spans) =>
                        spans
                            .filter((span) => span.getClientRects().length > 0)
                            .filter((span) => span.scrollWidth > span.clientWidth)
                            .map((span) => span.textContent),
                    );

                expect(cut, `Migas recortadas a ${width} px`).toEqual([]);
            }
        });

        test('no desborda a 768, 1024 y 1100 px', async ({ page }) => {
            for (const width of [768, 1024, 1100]) {
                await page.setViewportSize({ width, height: 900 });
                await noOverflowIn(page, ` a ${width} px`);
            }
        });
    });
}
