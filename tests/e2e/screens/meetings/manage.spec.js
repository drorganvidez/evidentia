import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import { auditThemes } from './helpers.js';

const PAGES = [
    ['/secretary/meeting/manage', 'Reuniones', 'Resumen'],
    ['/secretary/meeting/manage/request/list', 'Convocatorias', 'Convocatorias'],
    ['/secretary/meeting/manage/signaturesheet/list', 'Hojas de firmas', 'Hojas de firmas'],
    ['/secretary/meeting/manage/minutes/list', 'Actas', 'Actas'],
];

test.describe('Gestión de reuniones', () => {
    // Two themes, two audits and a reload per screen
    test.slow();

    test.beforeEach(async ({ page }) => {
        await signIn(page, 'secretario');
    });

    test('el resumen enlaza las cifras, la próxima convocatoria y la última acta', async ({
        page,
    }) => {
        await page.goto('/secretary/meeting/manage');

        await expect(page.getByRole('heading', { level: 1, name: 'Reuniones' })).toBeVisible();
        await expect(page.getByRole('main')).toHaveAttribute('aria-labelledby', 'page-title');
        const tabs = page.getByRole('navigation', { name: 'Secciones de las reuniones' });
        await expect(tabs.getByRole('link', { name: 'Resumen' })).toHaveAttribute(
            'aria-current',
            'page',
        );
        await expect(page.getByRole('link', { name: /^Convocatorias\s+\d+/ })).toBeVisible();
        await expect(
            page.getByRole('link', { name: 'Convocatoria de logística', exact: true }),
        ).toBeVisible();
        await expect(
            page.getByRole('link', { name: 'Reunión de logística', exact: true }),
        ).toBeVisible();

        await tabs.getByRole('link', { name: 'Actas' }).click();
        await expect(page.getByRole('heading', { level: 1, name: 'Actas' })).toBeVisible();
        await expect(tabs.getByRole('link', { name: 'Actas' })).toHaveAttribute(
            'aria-current',
            'page',
        );
    });

    test('una fila de la lista abre su detalle y el filtro rápido la encuentra', async ({
        page,
    }) => {
        await page.goto('/secretary/meeting/manage/signaturesheet/list');

        await page.getByRole('searchbox', { name: 'Filtrar hojas de firmas' }).fill('nada así');
        await expect(
            page.getByRole('link', { name: 'Hoja de firmas de logística', exact: true }),
        ).toBeHidden();
        await page.getByRole('searchbox', { name: 'Filtrar hojas de firmas' }).fill('logística');
        await page.getByRole('link', { name: 'Hoja de firmas de logística', exact: true }).click();

        await expect(
            page.getByRole('heading', { level: 1, name: 'Hoja de firmas de logística' }),
        ).toBeVisible();
        await expect(page.getByRole('table', { name: 'Firmas' })).toContainText('Lucía');
        await expect(page.getByRole('link', { name: /\/sign\/7391$/ })).toBeVisible();
    });

    for (const [path, title, tab] of PAGES) {
        test(`${title} cumple WCAG 2.2 AA en claro y oscuro sin desbordes`, async ({
            page,
            ui,
        }) => {
            await page.goto(path);
            await expect(page.getByRole('heading', { level: 1, name: title })).toBeVisible();
            await expect(
                page
                    .getByRole('navigation', { name: 'Secciones de las reuniones' })
                    .getByRole('link', { name: tab }),
            ).toHaveAttribute('aria-current', 'page');
            await auditThemes(page, ui);
        });
    }
});
