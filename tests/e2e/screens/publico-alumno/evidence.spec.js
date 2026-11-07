import { signIn } from '../../fixtures/auth.js';
import { choose, expect, test } from '../../fixtures/ui.js';

async function audit(page, ui) {
    await ui.noOverflow();

    for (const theme of ['light', 'dark']) {
        await ui.setTheme(theme);
        expect(await ui.axe(), `axe en ${theme}`).toEqual([]);
    }
}

/** The detail of an evidence of the seed reached from the list of the student. */
async function openFromList(page, title) {
    await page.goto('/evidence/list');
    await page
        .getByRole('table', { name: 'Mis evidencias' })
        .getByRole('link', { name: title, exact: true })
        .click();
    await expect(page.getByRole('heading', { level: 1 })).toHaveText(title);
}

test.describe('Evidencias de la estudiante', () => {
    test('descarga la copia de sus evidencias', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'La descarga solo en escritorio');
        await signIn(page, 'estudiante');
        await page.goto('/evidence/list');

        const download = page.waitForEvent('download');
        await page.locator('#evidence-archive').click();
        expect((await download).suggestedFilename()).toMatch(/^mis-evidencias-.*\.zip$/);
    });

    test('el listado enseña cada evidencia con su estado y se filtra', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/evidence/list');

        const table = page.getByRole('table', { name: 'Mis evidencias' });

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Mis evidencias');
        await expect(
            table.getByRole('link', { name: 'Recogida de material', exact: true }),
        ).toBeVisible();
        await expect(table.getByText('Atención en el registro')).toHaveCount(0);

        const search = page.getByRole('searchbox', { name: 'Buscar evidencias' });
        await search.fill('photocall');
        await search.press('Enter');
        await expect(page).toHaveURL(/\/evidence\/list\?q=photocall$/);
        await expect(
            table.getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Cartelería del vestíbulo', exact: true }),
        ).toHaveCount(0);
        await audit(page, ui);
    });

    test('el detalle de una rechazada enseña el motivo, el recorrido y el historial', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'estudiante');
        await openFromList(page, 'Recogida de material');

        await expect(
            page.getByRole('note').filter({ hasText: 'Motivo del rechazo' }),
        ).toContainText('Faltan las fotos del montaje terminado.');
        await expect(page.getByRole('list', { name: 'Recorrido de la evidencia' })).toBeVisible();
        await expect(page.getByRole('list', { name: 'Historial de la evidencia' })).toContainText(
            'Rechazada',
        );
        await expect(page.getByRole('button', { name: 'Corregir' })).toBeVisible();
        await audit(page, ui);
    });

    test('el formulario de una evidencia nueva se ve entero y accesible', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/evidence/create');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Nueva evidencia');
        await expect(page.getByLabel('Título')).toBeVisible();
        await expect(page.getByRole('group', { name: 'Duración' })).toBeVisible();
        await expect(page.getByRole('combobox', { name: 'Comité', exact: true })).toBeVisible();
        await audit(page, ui);
    });
});

test.describe('Una evidencia de principio a fin', () => {
    // Only on the desktop project and on data of its own (a unique title), so the order of the tests and a second
    // run against the same seed never matter
    test.skip(({ isMobile }) => isMobile);

    test('se guarda con una prueba, se publica, se rechaza, se corrige y se borra', async ({
        page,
        ui,
    }) => {
        const title = `Guion de la mesa redonda ${Date.now()}`;

        await signIn(page, 'estudiante');
        await page.goto('/evidence/create');
        await page.getByLabel('Título').fill(title);
        await page.getByLabel('Horas').fill('1');
        await page.getByLabel('Minutos').fill('30');
        await choose(page.getByRole('combobox', { name: 'Comité', exact: true }), 'Logística');
        await page.getByLabel('Descripción').fill('Preguntas y tiempos para las tres ponentes.');
        await page.getByLabel('Arrastra aquí las pruebas o pulsa para elegirlas').setInputFiles({
            name: 'guion.pdf',
            mimeType: 'application/pdf',
            buffer: Buffer.from('%PDF-1.4 guion'),
        });
        await expect(page.getByText('Subido', { exact: true })).toBeVisible();
        await page.getByRole('button', { name: 'Guardar borrador' }).click();

        await expect(page).toHaveURL(/\/evidence\/list$/);
        await expect(page.getByText('Evidencia creada con éxito.').first()).toBeVisible();

        await openFromList(page, title);
        await expect(page.getByRole('list', { name: 'Pruebas de la evidencia' })).toContainText(
            'guion.pdf',
        );
        await expect(page.getByRole('definition').filter({ hasText: '1,50 h' })).toBeVisible();

        // The proof is previewed in a modal of the same page, the PDF in a frame the policy allows
        await page.getByRole('button', { name: 'Ver guion.pdf' }).click();
        const preview = page.getByRole('dialog', { name: 'guion.pdf' });
        await expect(preview).toBeVisible();
        await expect(preview.locator('iframe[title="guion.pdf"]')).toBeVisible();
        await expect(preview.getByRole('link', { name: 'Abrir en otra pestaña' })).toHaveAttribute(
            'target',
            '_blank',
        );
        expect(await ui.axe(), 'axe con la vista previa abierta').toEqual([]);
        await page.keyboard.press('Escape');
        await expect(preview).toBeHidden();

        await page.getByRole('link', { name: 'Editar', exact: true }).click();
        await expect(page.getByLabel('Descripción')).toHaveValue(
            'Preguntas y tiempos para las tres ponentes.',
        );
        await page.getByRole('button', { name: 'Publicar', exact: true }).click();
        await page.getByRole('alertdialog').getByRole('button', { name: 'Sí, publicar' }).click();
        await expect(page.getByText('Evidencia publicada con éxito.').first()).toBeVisible();

        // The coordinator of the committee finds it in the queue of the home page and rejects it
        await page.context().clearCookies();
        await signIn(page, 'coordinador');
        await page.goto('/');
        await page
            .getByRole('region', { name: 'Por revisar en Logística' })
            .getByRole('link', { name: title })
            .click();
        await page.getByRole('button', { name: 'Rechazar' }).click();
        await page.getByLabel('Motivo del rechazo').fill('Falta la hoja de turnos firmada.');
        // The review lists belong to another group and still use the old layout: the rejection really posts, and
        // the page it redirects to is replaced by a stub
        let redirect = null;

        await page.route(
            (url) => url.pathname.replace(/\/$/, '') === '/coordinator/evidence/reject',
            async (route) => {
                const response = await route.fetch({ maxRedirects: 0 });

                redirect = response.headers().location;
                await route.fulfill({
                    contentType: 'text/html',
                    body: '<!doctype html><html lang="es"><title>Rechazada</title></html>',
                });
            },
        );
        await page.getByRole('dialog').getByRole('button', { name: 'Rechazar' }).click();
        await expect.poll(() => redirect).toMatch(/\/coordinator\/evidence\/list\/rejected$/);

        // The student reads the reason, takes it back to draft and finally deletes it
        await page.context().clearCookies();
        await signIn(page, 'estudiante');
        await openFromList(page, title);
        await expect(
            page.getByRole('note').filter({ hasText: 'Motivo del rechazo' }),
        ).toContainText('Falta la hoja de turnos firmada.');
        await page.getByRole('button', { name: 'Corregir' }).click();
        await page.getByRole('alertdialog').getByRole('button', { name: 'Sí, corregir' }).click();
        await expect(
            page.getByText('Evidencia reasignada como borrador con éxito.').first(),
        ).toBeVisible();

        await page.getByRole('button', { name: `Eliminar ${title}` }).click();
        await page.getByRole('alertdialog').getByRole('button', { name: 'Sí, eliminar' }).click();
        await expect(page.getByText('Evidencia borrada con éxito.').first()).toBeVisible();
        await expect(
            page.getByRole('table', { name: 'Mis evidencias' }).getByText(title),
        ).toHaveCount(0);
    });
});
