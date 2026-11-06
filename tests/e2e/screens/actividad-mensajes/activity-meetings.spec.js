import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import { audit } from './helpers.js';

/** The id in the link «Historial de …» of a row of the lists of the secretary. */
async function historyHref(page, list, name) {
    await page.goto(list);

    // the actions of a row hide on the phone, so the link is read from the markup
    return page.locator(`a[aria-label="Historial de ${name}"]`).getAttribute('href');
}

// The detail and the history of a minutes and of a meeting request
test.describe('Fichas de actas y convocatorias', () => {
    // several pages and two audits of axe per test, slow on the emulated phone
    test.slow();

    test('el secretario abre el acta con sus puntos, asistentes e historial', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'secretario');
        const href = await historyHref(
            page,
            '/secretary/meeting/manage/minutes/list',
            'Reunión de logística',
        );
        expect(href).toMatch(/\/activity\/minutes\/\d+\?tab=historial$/);
        await page.goto(href.replace('?tab=historial', ''));

        await expect(
            page.getByRole('heading', { level: 1, name: 'Reunión de logística' }),
        ).toBeVisible();
        await expect(page.getByRole('link', { name: 'Editar' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Descargar PDF' })).toBeVisible();
        await expect(page.getByRole('region', { name: 'Asistentes' })).toContainText('Lucía');

        const point = page.locator('details').filter({ hasText: 'Reparto de tareas' });
        await point.locator('summary').focus();
        await page.keyboard.press('Enter');
        await expect(point.getByText('Cada equipo nombra un responsable.')).toBeVisible();
        await page.keyboard.press('Enter');
        await expect(point.getByText('Cada equipo nombra un responsable.')).toBeHidden();

        // the seed stores no PDF files: only the link is checked
        await expect(page.getByRole('link', { name: 'Descargar PDF' })).toHaveAttribute(
            'href',
            /\/minutes\/download\/\d+$/,
        );

        await page
            .getByRole('navigation', { name: 'Secciones del acta' })
            .getByRole('link', { name: 'Historial' })
            .click();
        await expect(page).toHaveURL(/tab=historial$/);
        await audit(page, ui);
    });

    test('una compañera sin relación ve el detalle sin historial', async ({ page, ui }) => {
        await signIn(page, 'secretario');
        const href = await historyHref(
            page,
            '/secretary/meeting/manage/minutes/list',
            'Reunión de logística',
        );
        await page.context().clearCookies();
        await signIn(page, 'companero');
        await page.goto(href);

        await expect(page.getByRole('region', { name: 'Puntos y acuerdos' })).toBeVisible();
        await expect(page.getByRole('navigation', { name: 'Secciones del acta' })).toHaveCount(0);
        await expect(page.getByRole('link', { name: 'Escribir al secretario' })).toHaveAttribute(
            'href',
            /\/messages\/create\?minutes=\d+$/,
        );
        await expect(page.getByRole('link', { name: 'Editar' })).toHaveCount(0);
        await audit(page, ui);
    });

    test('el secretario ve el orden del día, las firmas y el historial de la convocatoria', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'secretario');
        const href = await historyHref(
            page,
            '/secretary/meeting/manage/request/list',
            'Convocatoria de logística',
        );
        await page.goto(href.replace('?tab=historial', ''));

        await expect(
            page.getByRole('heading', { level: 1, name: 'Convocatoria de logística' }),
        ).toBeVisible();
        await expect(
            page.getByRole('region', { name: 'Orden del día' }).getByRole('listitem'),
        ).toContainText(['Reparto de tareas']);
        await expect(page.getByRole('region', { name: 'Firmas' })).toContainText('Lucía');
        await expect(page.getByRole('link', { name: 'Hoja de firmas' })).toBeVisible();

        await page.goto(href);
        await expect(
            page.getByRole('list', { name: 'Historial de la convocatoria' }),
        ).toContainText('Convocatoria creada');
        await audit(page, ui);
    });

    test('la estudiante ve la convocatoria sin nombres de firmas', async ({ page, ui }) => {
        await signIn(page, 'secretario');
        const href = await historyHref(
            page,
            '/secretary/meeting/manage/request/list',
            'Convocatoria de logística',
        );
        await page.context().clearCookies();
        await signIn(page, 'estudiante');
        await page.goto(href);

        await expect(page.getByRole('region', { name: 'Orden del día' })).toContainText(
            'Reparto de tareas',
        );
        await expect(page.getByRole('region', { name: 'Firmas' })).toHaveCount(0);
        await expect(
            page.getByRole('navigation', { name: 'Secciones de la convocatoria' }),
        ).toHaveCount(0);
        await expect(page.getByRole('link', { name: 'Escribir al secretario' })).toBeVisible();
        await audit(page, ui);
    });
});
