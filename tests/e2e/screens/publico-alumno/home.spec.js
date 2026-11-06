import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

async function audit(page, ui) {
    await ui.noOverflow();

    for (const theme of ['light', 'dark']) {
        await ui.setTheme(theme);
        expect(await ui.axe(), `axe en ${theme}`).toEqual([]);
    }
}

test.describe('Inicio', () => {
    test('la estudiante ve sus horas, sus últimas evidencias y los plazos', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'estudiante');
        await page.goto('/');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Hola, Lucía');
        await expect(page.getByText('Horas computadas')).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Plazos' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Por revisar en Logística' })).toHaveCount(
            0,
        );
        await audit(page, ui);

        // The documents of each committee open in place
        await page.getByText('Comité de Logística').click();
        await expect(page.getByRole('link', { name: 'Convocatoria de logística' })).toBeVisible();

        await expect(page.getByRole('link', { name: 'Montaje del photocall' })).toHaveAttribute(
            'href',
            /\/evidence\/view\/\d+$/,
        );
    });

    test('el coordinador ve la cola de su comité sin sus propias evidencias', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'coordinador');
        await page.goto('/');

        const queue = page.getByRole('region', { name: 'Por revisar en Logística' });

        await expect(queue.getByRole('link', { name: 'Atención en el registro' })).toBeVisible();
        await expect(queue.getByRole('link', { name: 'Ver las pendientes' })).toHaveAttribute(
            'href',
            /\/coordinator\/evidence\/list\/pending$/,
        );
        await audit(page, ui);
    });

    for (const [who, name, prefix] of [
        ['presidente', 'Elena', 'president'],
        ['profesor', 'David', 'lecture'],
    ]) {
        test(`${who} tiene el panel del curso con cifras y trazabilidad`, async ({ page, ui }) => {
            await signIn(page, who);
            await page.goto('/');

            await expect(page.getByRole('heading', { level: 1 })).toHaveText('Panel del curso');
            await expect(page.getByText(`Hola, ${name}`)).toBeVisible();
            await expect(page.getByRole('link', { name: /Por revisar/ })).toHaveAttribute(
                'href',
                new RegExp(`/${prefix}/evidence/list$`),
            );
            await expect(
                page.getByRole('list', { name: 'Estudiantes por tramo de horas acreditadas' }),
            ).toBeVisible();
            await expect(page.getByRole('table', { name: 'Comités' })).toContainText('Logística');
            await expect(
                page.getByRole('list', { name: 'Evidencias enviadas en las últimas ocho semanas' }),
            ).toBeVisible();
            await expect(page.getByRole('region', { name: 'Señales del registro' })).toBeVisible();
            await expect(page.getByRole('region', { name: 'Errores del servidor' })).toHaveCount(1);
            await audit(page, ui);
        });
    }
});
