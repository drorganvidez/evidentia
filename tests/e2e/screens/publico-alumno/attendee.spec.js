import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

async function audit(page, ui, selector = null) {
    await ui.noOverflow();

    for (const theme of ['light', 'dark']) {
        await ui.setTheme(theme);
        expect(await ui.axe(selector), `axe en ${theme}`).toEqual([]);
    }
}

test.describe('Mis asistencias', () => {
    test('enseña los eventos con sus horas y su asistencia', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/attendee/list');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Mis asistencias');
        await expect(page.getByRole('table', { name: 'Mis asistencias' })).toContainText(
            'Taller de Git',
        );
        await expect(page.getByText('Asistido', { exact: true })).toBeVisible();
        await audit(page, ui);
    });
});
