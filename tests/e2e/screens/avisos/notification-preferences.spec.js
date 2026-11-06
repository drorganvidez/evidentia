import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// Which notices also arrive by mail: the fixed ones locked, the rest by level, the digest only for the people in charge
test.describe('Preferencias de avisos', () => {
    test('el coordinador ve los fijos bloqueados y el resumen diario', async ({ page, ui }) => {
        await signIn(page, 'coordinador');
        await page.goto('/notifications/preferences');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Preferencias de avisos');
        await expect(page.getByRole('switch', { name: 'Evidencia rechazada' })).toBeDisabled();
        await expect(page.getByRole('switch', { name: 'Evidencia rechazada' })).toBeChecked();
        await expect(
            page.getByRole('switch', { name: 'Evidencia recibida en tu comité' }),
        ).toBeEnabled();
        await expect(page.getByRole('heading', { name: 'Resumen diario' })).toBeVisible();
        await ui.noOverflow();

        for (const theme of ['light', 'dark']) {
            await ui.setTheme(theme);
            expect(await ui.axe(), `axe en ${theme}`).toEqual([]);
        }
    });

    test('la estudiante no tiene resumen diario y llega desde su perfil', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/profile');
        await page.getByRole('link', { name: 'Preferencias de avisos' }).click();

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Preferencias de avisos');
        await expect(page.getByRole('switch', { name: 'Evidencia aceptada' })).toBeEnabled();
        await expect(page.getByRole('heading', { name: 'Resumen diario' })).toHaveCount(0);
        await ui.noOverflow();
    });

    test('el coordinador cambia el correo y el resumen y se conservan', async ({
        page,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        await signIn(page, 'coordinador');
        await page.goto('/notifications/preferences');

        await page
            .getByRole('switch', { name: 'Evidencia recibida en tu comité' })
            .setChecked(false);
        await page.getByRole('switch', { name: /^Un correo al día/ }).setChecked(true);
        await page.getByRole('button', { name: 'Guardar' }).click();

        await expect(
            page.getByRole('region', { name: 'Avisos' }).getByText('Preferencias guardadas.'),
        ).toBeVisible();
        await page.reload();
        await expect(
            page.getByRole('switch', { name: 'Evidencia recibida en tu comité' }),
        ).not.toBeChecked();
        await expect(page.getByRole('switch', { name: /^Un correo al día/ })).toBeChecked();
    });
});
