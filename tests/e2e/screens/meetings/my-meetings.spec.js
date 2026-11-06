import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import { auditThemes } from './helpers.js';

test.describe('Mis reuniones', () => {
    // Two themes, two audits and a reload per screen
    test.slow();

    test('el alumno ve sus reuniones con las horas y el plazo de registro', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'estudiante');
        await page.goto('/meeting/list');

        await expect(page.getByRole('heading', { level: 1, name: 'Mis reuniones' })).toBeVisible();
        const table = page.getByRole('table', { name: 'Mis reuniones' });
        await expect(table).toContainText('Reunión de logística');
        await expect(table).toContainText('2,00 h');
        await expect(page.getByRole('main').getByText('Registro de reuniones')).toBeVisible();
        await auditThemes(page, ui);
    });

    test('quien no asistió a ninguna ve el estado vacío', async ({ page, ui }) => {
        // Nobody adds the president to a meeting, not even the flows of the other specs
        await signIn(page, 'presidente');
        await page.goto('/meeting/list');

        await expect(page.getByRole('heading', { name: 'Sin reuniones' })).toBeVisible();
        await auditThemes(page, ui);
    });
});
