import { readFile } from 'node:fs/promises';
import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// The record of a student for the management: the summary, the account and the accesses on top, and every section
// by its address, with the links to the page of each thing and the download as a CSV. The first person of the seed
// is Lucía Pérez Romero; the counts of the activity are never read, other seeds add annotations.
const RECORD = '/profiles/view/1';

async function audit(page, ui) {
    await ui.noOverflow();

    for (const theme of ['light', 'dark']) {
        await ui.setTheme(theme);
        expect(await ui.axe(), `axe en ${theme}`).toEqual([]);
    }
}

function sections(page) {
    return page.getByRole('navigation', { name: 'Secciones del expediente' });
}

test.describe('Expediente de un estudiante', () => {
    test('la presidenta ve el resumen, la cuenta y las evidencias enviadas', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'presidente');
        await page.goto(RECORD);

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Lucía Pérez Romero');
        await expect(page.getByText('Horas computadas')).toBeVisible();
        const account = page.getByRole('region', { name: 'Cuenta' });
        await expect(account.getByText('estudiante@e2e.test')).toBeVisible();
        await expect(account.getByText('Equipo de montaje')).toBeVisible();
        await expect(account.getByText('Último acceso')).toBeVisible();

        const current = sections(page).getByRole('link', { name: /^Evidencias/ });
        await expect(current).toHaveAttribute('aria-current', 'page');
        const evidences = page.getByRole('table', { name: 'Evidencias enviadas' });
        await expect(
            evidences.getByRole('link', { name: 'Cartelería del vestíbulo', exact: true }),
        ).toBeVisible();
        await expect(
            evidences.getByRole('link', { name: 'Historial de Recogida de material' }),
        ).toBeVisible();
        await expect(evidences.getByText('Borrador de la memoria')).toHaveCount(0);
        await expect(
            page.getByText('3 enviadas · 1 aceptada · 1 pendiente · 1 rechazada'),
        ).toBeVisible();
        await audit(page, ui);
    });

    test('el profesor recorre cada sección por su pestaña', async ({ page, ui }) => {
        // Six pages one after another
        test.setTimeout(60_000);
        await signIn(page, 'profesor');
        await page.goto(RECORD);

        const visits = [
            [
                'Reuniones',
                'reuniones',
                () =>
                    page
                        .getByRole('table', { name: 'Reuniones' })
                        .getByRole('link', { name: 'Reunión de logística' }),
            ],
            [
                'Firmas',
                'firmas',
                () =>
                    page
                        .getByRole('table', { name: 'Convocatorias de sus comités' })
                        .getByText('Firmó'),
            ],
            [
                'Asistencias',
                'asistencias',
                () => page.getByRole('table', { name: 'Asistencias' }).getByText('Taller de Git'),
            ],
            [
                'Bonos',
                'bonos',
                () => page.getByRole('table', { name: 'Bonos' }).getByText('Montaje del escenario'),
            ],
            [
                'Actividad',
                'actividad',
                () =>
                    page
                        .getByRole('list', { name: 'Actividad de Lucía Pérez Romero' })
                        .getByText('Evidencia rechazada')
                        .first(),
            ],
            [
                'Mensajes',
                'mensajes',
                () =>
                    page
                        .getByRole('table', { name: 'Mensajes que ha escrito' })
                        .getByRole('link', { name: 'Duda sobre el photocall' }),
            ],
        ];

        for (const [label, key, content] of visits) {
            await sections(page)
                .getByRole('link', { name: new RegExp(`^${label}`) })
                .click();
            await expect(page).toHaveURL(new RegExp(`tab=${key}#secciones$`));
            const current = sections(page).getByRole('link', { name: new RegExp(`^${label}`) });
            await expect(current).toHaveAttribute('aria-current', 'page');
            await expect(current).toBeInViewport();
            await expect(content()).toBeVisible();
            await ui.noOverflow();
        }
    });

    for (const key of ['reuniones', 'firmas', 'asistencias', 'bonos', 'actividad', 'mensajes']) {
        test(`la sección ${key} pasa axe en claro y oscuro`, async ({ page, ui }) => {
            await signIn(page, 'presidente');
            await page.goto(`${RECORD}?tab=${key}`);

            await expect(page.getByRole('heading', { level: 1 })).toHaveText('Lucía Pérez Romero');
            await audit(page, ui);
        });
    }

    test('en el teléfono cada objetivo de cada sección mide al menos 44 px', async ({
        page,
    }, testInfo) => {
        test.skip(
            testInfo.project.name !== 'mobile',
            'El objetivo de 44 px se exige con puntero grueso',
        );
        test.setTimeout(60_000);
        await signIn(page, 'presidente');

        for (const query of [
            '',
            '?tab=reuniones',
            '?tab=firmas',
            '?tab=bonos',
            '?tab=actividad',
            '?tab=mensajes',
        ]) {
            await page.goto(`${RECORD}${query}`);
            const small = await page.locator('main').evaluate((main) =>
                Array.from(main.querySelectorAll('a[href], button'))
                    .filter((el) => el.checkVisibility())
                    .map((el) => ({ el, box: el.getBoundingClientRect() }))
                    .filter(({ box }) => box.width < 43.5 || box.height < 43.5)
                    .map(
                        ({ el, box }) =>
                            `«${el.textContent.trim()}» ${Math.round(box.width)} × ${Math.round(box.height)}`,
                    ),
            );

            expect(small, query || '?tab=evidencias').toEqual([]);
        }
    });

    test('las filas llevan a la ficha de cada cosa', async ({ page }) => {
        await signIn(page, 'presidente');

        await page.goto(`${RECORD}?tab=reuniones`);
        await page.getByRole('link', { name: 'Reunión de logística' }).click();
        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Reunión de logística');

        await page.goto(RECORD);
        await page.getByRole('link', { name: 'Historial de Cartelería del vestíbulo' }).click();
        await expect(page).toHaveURL(/\/activity\/evidence\/\d+$/);

        await page.goto(`${RECORD}?tab=firmas`);
        await page
            .getByRole('table', { name: 'Hojas firmadas' })
            .getByRole('link', { name: 'Hoja de firmas de logística' })
            .click();
        await expect(page.getByRole('heading', { level: 1 })).toHaveText(
            'Convocatoria de logística',
        );

        await page.goto(`${RECORD}?tab=bonos`);
        await page
            .getByRole('table', { name: 'Bonos' })
            .getByRole('link', { name: 'Logística' })
            .click();
        await expect(page.getByRole('heading', { level: 1, name: 'Logística' })).toBeVisible();
    });

    test('la actividad se acota por ámbito con la pestaña a la vista', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto(`${RECORD}?tab=actividad`);

        const scopes = page.getByRole('navigation', { name: 'Ámbito de la actividad' });
        await scopes.getByRole('link', { name: /^Lo que le afecta/ }).click();
        await expect(page).toHaveURL(/ambito=afecta/);
        const current = scopes.getByRole('link', { name: /^Lo que le afecta/ });
        await expect(current).toHaveAttribute('aria-current', 'page');
        await expect(current).toBeInViewport();
        await expect(sections(page).getByRole('link', { name: /^Actividad/ })).toBeInViewport();

        const log = page.getByRole('list', { name: 'Actividad de Lucía Pérez Romero' });
        await expect(log.getByText('Evidencia rechazada').first()).toBeVisible();
        await expect(
            log.getByText('por Carlos Navarro Ruiz como coordinador de Logística').first(),
        ).toBeVisible();
        await expect(log.getByRole('link', { name: 'Recogida de material' }).first()).toBeVisible();

        await scopes.getByRole('link', { name: /^Accesos/ }).click();
        await expect(page).toHaveURL(/ambito=accesos/);
        await ui.noOverflow();
    });

    test('descarga el expediente en CSV y la descarga queda anotada', async ({ page }) => {
        await signIn(page, 'presidente');
        await page.goto(RECORD);

        const download = page.waitForEvent('download');
        await page.getByRole('link', { name: 'Descargar expediente' }).click();
        const file = await download;
        expect(file.suggestedFilename()).toMatch(
            /^expediente-lucia-perez-romero-\d{4}-\d{2}-\d{2}\.csv$/,
        );
        const csv = await readFile(await file.path(), 'utf8');
        expect(csv.startsWith('﻿seccion;fecha;concepto;comite;estado;horas;detalle')).toBe(true);
        expect(csv).toContain('Cartelería del vestíbulo');
        expect(csv).toContain('Taller de Git');
        expect(csv).not.toContain('Borrador de la memoria');

        await page.goto(`${RECORD}?tab=actividad&ambito=afecta`);
        await expect(
            page
                .getByRole('list', { name: 'Actividad de Lucía Pérez Romero' })
                .getByText('Exportación del expediente')
                .first(),
        ).toBeVisible();
    });

    test('no existe para el resto de roles', async ({ page }) => {
        for (const who of ['estudiante', 'coordinador', 'secretario', 'registro']) {
            await page.context().clearCookies();
            await signIn(page, who);
            // Through the request context: a 404 document would log a console error in the page
            expect((await page.request.get(RECORD)).status(), who).toBe(404);
            expect((await page.request.get(`${RECORD}/export`)).status(), who).toBe(404);
        }
    });
});
