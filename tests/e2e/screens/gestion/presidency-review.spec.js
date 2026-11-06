import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

/**
 * The queue of the presidency (App\Services\ReviewQueue): what Inés, the second coordinator of Logística, sends to her
 * committee and what Marta sends to the committee of the presidency. The presidents review it as coordinators, with
 * its count in the navigation, its card in the control panel and its weekly summary in the notices.
 */
test.describe('Revisión de la presidencia', () => {
    test('enseña las evidencias de la cola con su comité', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/review/all');

        await expect(
            page.getByRole('heading', { level: 1, name: 'Revisar evidencias' }),
        ).toBeVisible();
        const table = page.getByRole('region', { name: 'Evidencias de Presidencia' });
        await expect(
            table.getByRole('link', { name: 'Inventario del almacén', exact: true }),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Acreditación de ponentes', exact: true }),
        ).toBeVisible();
        await expect(table.getByText('Montaje del photocall')).toHaveCount(0);
        await expect(
            table.getByRole('row').filter({ hasText: 'Inventario del almacén' }),
        ).toContainText('Logística');
        await ui.noOverflow();

        const tabs = page.getByRole('navigation', { name: 'Filtrar evidencias por estado' });
        await tabs.getByRole('link', { name: /Pendientes/ }).click();
        await expect(page).toHaveURL(/\/president\/review\/pending$/);
        await expect(
            table.getByRole('link', { name: 'Inventario del almacén', exact: true }),
        ).toBeVisible();
    });

    test('cuenta las pendientes en la navegación y enlaza cada fila', async ({
        page,
        isMobile,
    }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/review/pending');

        const nav = page.getByRole('navigation', {
            name: isMobile ? 'Navegación principal (móvil)' : 'Gestión',
            exact: true,
        });
        await expect(nav.getByRole('link', { name: /Revisar/ })).toHaveAttribute(
            'href',
            /\/president\/review\/all$/,
        );
        // the bar of the phone has no counts
        if (!isMobile) {
            await expect(nav.getByRole('link', { name: /Revisar/ })).toContainText(
                /\d+ pendientes?/,
            );
        }
        await expect(
            page.getByRole('link', { name: 'Inventario del almacén', exact: true }),
        ).toHaveAttribute('href', /\/president\/review\/view\/\d+$/);
    });

    test('abre la evidencia con las acciones de la presidencia', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/review/pending');
        await page.getByRole('link', { name: 'Inventario del almacén', exact: true }).click();

        await expect(page).toHaveURL(/\/president\/review\/view\/\d+$/);
        await expect(
            page.getByRole('heading', { level: 1, name: 'Inventario del almacén' }),
        ).toBeVisible();
        await expect(page.getByRole('button', { name: 'Aceptar', exact: true })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Rechazar', exact: true })).toBeVisible();
        await ui.noOverflow();
    });

    test('pasa axe en claro y oscuro', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/review/all');
        expect(await ui.axe()).toEqual([]);

        await ui.setTheme('dark');
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });

    test('el profesor también puede revisarla', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/president/review/all');

        await expect(
            page
                .getByRole('region', { name: 'Evidencias de Presidencia' })
                .getByRole('link', { name: 'Inventario del almacén', exact: true }),
        ).toBeVisible();
        await ui.noOverflow();
    });

    test('rechaza con motivo y vuelve a aceptar', async ({ page, ui }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        await signIn(page, 'presidente');
        await page.goto('/president/review/all');
        await page.getByRole('button', { name: 'Rechazar Acreditación de ponentes' }).click();

        const dialog = page.getByRole('dialog', { name: 'Rechazar la evidencia' });
        await expect(dialog).toBeVisible();
        expect(await ui.axe()).toEqual([]);
        await dialog.getByLabel('Motivo del rechazo').fill('Falta la lista firmada de ponentes.');
        await dialog.getByRole('button', { name: 'Rechazar', exact: true }).click();

        await expect(page).toHaveURL(/\/president\/review\/rejected$/);
        const notices = page.getByRole('region', { name: 'Avisos' });
        await expect(notices.getByText('Evidencia rechazada con éxito.')).toBeVisible();
        const table = page.getByRole('region', { name: 'Evidencias de Presidencia' });
        await expect(
            table.getByRole('link', { name: 'Acreditación de ponentes', exact: true }),
        ).toBeVisible();

        await table.getByRole('button', { name: 'Aceptar Acreditación de ponentes' }).click();
        await expect(page).toHaveURL(/\/president\/review\/accepted$/);
        await expect(notices.getByText('Evidencia aceptada con éxito.')).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Acreditación de ponentes', exact: true }),
        ).toBeVisible();
    });
});

test.describe('Enrutado de las evidencias de la coordinación', () => {
    test('el coordinador no ve en su cola lo que envía la otra coordinadora', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'coordinador');
        await page.goto('/coordinator/evidence/list/all');

        const table = page.getByRole('region', { name: 'Evidencias de Logística' });
        await expect(
            table.getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toBeVisible();
        await expect(table.getByText('Inventario del almacén')).toHaveCount(0);
        await ui.noOverflow();
    });

    test('la presidencia tiene su cola en el panel del curso', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/');

        const card = page.getByRole('region', { name: 'Cola de presidencia' });
        await expect(
            card.getByRole('link', { name: 'Inventario del almacén', exact: true }),
        ).toHaveAttribute('href', /\/president\/review\/view\/\d+$/);
        await expect(card.getByRole('link', { name: 'Abrir la cola' })).toHaveAttribute(
            'href',
            /\/president\/review\/pending$/,
        );
        await expect(
            page.getByRole('link', { name: 'Revisar evidencias', exact: true }).first(),
        ).toBeVisible();
        await ui.noOverflow();
    });

    test('el resumen semanal llega a la presidencia y lleva a su cola', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/notifications');

        const link = page
            .getByRole('list', { name: 'Lista de avisos' })
            .getByRole('link', { name: /^Resumen semanal · \d+ evidencias? por revisar/ })
            .first();
        await expect(link).toBeVisible();
        const response = await page.request.get((await link.getAttribute('href')) ?? '', {
            maxRedirects: 0,
        });

        expect(response.status()).toBe(302);
        expect(new URL(response.headers().location).pathname).toBe('/president/review/pending');
        await ui.noOverflow();

        for (const theme of ['light', 'dark']) {
            await ui.setTheme(theme);
            expect(await ui.axe(), `axe en ${theme}`).toEqual([]);
        }
    });
});
