import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// «Mi comité»: the workspace of the coordinator with the team, the figures, the members, the meetings and the export
test.describe('Mi comité', () => {
    test('enseña el equipo, las cifras y los miembros del comité', async ({ page, ui }) => {
        await signIn(page, 'coordinador');
        await page.goto('/coordinator/committee');

        await expect(page.getByRole('heading', { level: 1, name: 'Mi comité' })).toBeVisible();
        const team = page.getByRole('list', { name: 'Equipo de Logística' });
        await expect(team.getByText('Carlos Navarro Ruiz')).toBeVisible();
        await expect(team.getByText('Marta Ruiz Díaz')).toBeVisible();

        const pending = page.getByRole('link', { name: /Pendientes/ });
        await expect(pending).toHaveAttribute('href', /\/coordinator\/evidence\/list\/pending$/);
        await expect(pending).toContainText('2');
        await expect(page.getByRole('link', { name: /Horas aceptadas/ })).toContainText(
            '9,00 h acreditadas',
        );
        await expect(page.getByText('Miembros activos')).toBeVisible();

        const members = page.getByRole('region', { name: 'Miembros de Logística' });
        await expect(members.getByText('Pérez Romero, Lucía')).toBeVisible();
        await expect(members.getByText('García López, Juan')).toBeVisible();
        await expect(members.getByText('Ruiz Díaz, Marta')).toBeVisible();
        await expect(members.getByText('Navarro Ruiz, Carlos')).toHaveCount(0);
        await ui.noOverflow();
    });

    test('filtra los miembros en el navegador', async ({ page }) => {
        await signIn(page, 'coordinador');
        await page.goto('/coordinator/committee');

        await page.getByRole('searchbox', { name: 'Filtrar miembros' }).fill('rechazada');
        const members = page.getByRole('region', { name: 'Miembros de Logística' });
        await expect(members.getByText('Pérez Romero, Lucía')).toBeVisible();
        await expect(members.getByText('García López, Juan')).toBeHidden();
    });

    test('lleva a la revisión y enlaza los documentos de las reuniones', async ({ page }) => {
        await signIn(page, 'coordinador');
        await page.goto('/coordinator/committee');

        const meetings = page.getByRole('region', { name: 'Reuniones del comité' });
        await expect(
            meetings.getByRole('link', { name: 'Convocatoria de logística' }),
        ).toHaveAttribute('href', /\/download\/request\/\d+$/);
        await expect(meetings.getByRole('link', { name: 'Reunión de logística' })).toHaveAttribute(
            'href',
            /\/secretary\/meeting\/manage\/minutes\/download\/\d+$/,
        );

        await page.getByRole('link', { name: 'Revisar pendientes' }).click();
        await expect(page).toHaveURL(/\/coordinator\/evidence\/list\/pending$/);
    });

    test('pasa axe en claro y oscuro', async ({ page, ui }) => {
        await signIn(page, 'coordinador');
        await page.goto('/coordinator/committee');
        expect(await ui.axe()).toEqual([]);

        await ui.setTheme('dark');
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });

    test('exporta los miembros y deja la anotación en la actividad', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        await signIn(page, 'coordinador');
        await page.goto('/coordinator/committee');

        const download = page.waitForEvent('download');
        await page.getByRole('link', { name: 'Exportar' }).click();
        const file = await download;
        expect(file.suggestedFilename()).toMatch(/^comite-logistica-\d{4}-\d{2}-\d{2}\.csv$/);

        await page.goto('/coordinator/committee');
        await expect(
            page
                .getByRole('list', { name: 'Últimas acciones del comité' })
                .getByText('Exportación del comité')
                .first(),
        ).toBeVisible();
    });

    test('no existe para quien no coordina un comité', async ({ page }) => {
        await signIn(page, 'secretario');
        // Through the request context: a 404 document would log a console error in the page
        const response = await page.request.get('/coordinator/committee');

        expect(response.status()).toBe(404);
    });
});
