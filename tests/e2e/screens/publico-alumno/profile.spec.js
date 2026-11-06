import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

async function audit(page, ui, selector = null) {
    await ui.noOverflow();

    for (const theme of ['light', 'dark']) {
        await ui.setTheme(theme);
        expect(await ui.axe(selector), `axe en ${theme}`).toEqual([]);
    }
}

test.describe('Mi perfil', () => {
    test('enseña el resumen y los tres formularios', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/profile');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Mi perfil');
        await expect(page.getByRole('heading', { name: 'Lucía Pérez Romero' })).toBeVisible();
        await expect(page.getByLabel('Correo electrónico')).toHaveValue('estudiante@e2e.test');
        await expect(page.getByRole('group', { name: 'Nivel de implicación' })).toHaveCount(0);
        await expect(page.getByLabel('Contraseña actual')).toBeVisible();
        await audit(page, ui);
    });

    test('el profesor no tiene resumen del trabajo', async ({ page }) => {
        await signIn(page, 'profesor');
        await page.goto('/profile');

        await expect(page.getByRole('heading', { name: 'Resumen del trabajo' })).toHaveCount(0);
        await expect(page.getByRole('heading', { name: 'Contraseña' })).toBeVisible();
    });

    test.describe('cambios', () => {
        // Only on the desktop project; writing the same summary twice leaves the same profile, so a second run passes
        test.skip(({ isMobile }) => isMobile);

        test('el secretario escribe su resumen y lo ve en el perfil', async ({ page }) => {
            await signIn(page, 'secretario');
            await page.goto('/profile');

            await page
                .getByLabel('Resumen del trabajo en las jornadas')
                .fill('Convoqué las reuniones de logística.\n\nRedacté sus actas.');
            await page.getByRole('button', { name: 'Guardar el resumen' }).click();

            await expect(page.getByText('Biografía editada con éxito.').first()).toBeVisible();

            const summary = page.getByRole('region', { name: 'Marta Ruiz Díaz' });

            await expect(summary.getByText('Redacté sus actas.')).toBeVisible();
        });

        test('un correo ya usado se marca en su campo', async ({ page }) => {
            await signIn(page, 'secretario');
            await page.goto('/profile');

            await page.getByLabel('Correo electrónico').fill('estudiante@e2e.test');
            await page.getByRole('button', { name: 'Guardar los datos' }).click();

            await expect(page.getByLabel('Correo electrónico')).toHaveAttribute(
                'aria-invalid',
                'true',
            );
        });
    });
});

test.describe('Perfil de un estudiante para la gestión', () => {
    test('la presidenta abre el perfil y una evidencia aceptada', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        // The list of students belongs to another group: the profile of the first person of the seed by its address
        await page.goto('/profiles/view/1');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Lucía Pérez Romero');
        await audit(page, ui);

        await page
            .getByRole('table', { name: 'Evidencias enviadas' })
            .getByRole('link', { name: 'Cartelería del vestíbulo' })
            .click();
        await expect(page.getByRole('heading', { level: 1 })).toHaveText(
            'Cartelería del vestíbulo',
        );
        // The seed writes no seal, so its evidences read as altered
        await expect(page.getByRole('definition').filter({ hasText: 'Alterada' })).toBeVisible();
    });
});
