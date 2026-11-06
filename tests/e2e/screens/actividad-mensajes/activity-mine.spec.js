import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import { audit } from './helpers.js';

// The activity of each person: what they did, what others did on their things and their accesses
test.describe('Mi actividad', () => {
    // several pages and two audits of axe per test, slow on the emulated phone
    test.slow();

    test('la estudiante recorre los cuatro ámbitos', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/activity/mine');

        await expect(page.getByRole('heading', { level: 1, name: 'Mi actividad' })).toBeVisible();
        const tabs = page.getByRole('navigation', { name: 'Ámbito de mi actividad' });
        const history = page.getByRole('list', { name: 'Mi actividad' });
        await expect(tabs.getByRole('link', { name: /Todo/ })).toHaveAttribute(
            'aria-current',
            'page',
        );

        await tabs.getByRole('link', { name: /Accesos/ }).click();
        await expect(page).toHaveURL(/scope=access/);
        await expect(tabs.getByRole('link', { name: /Accesos/ })).toHaveAttribute(
            'aria-current',
            'page',
        );
        // signIn has just gone through the real login
        await expect(history.getByText('Inicio de sesión').first()).toBeVisible();

        await tabs.getByRole('link', { name: /Sobre mis cosas/ }).click();
        await expect(page).toHaveURL(/scope=about/);
        await expect(history.getByText('Evidencia rechazada').first()).toBeVisible();
        // the rejection and the notice of it both point to the evidence
        await expect(
            history.getByRole('link', { name: 'Recogida de material' }).first(),
        ).toHaveAttribute('href', /\/activity\/evidence\/\d+$/);

        await tabs.getByRole('link', { name: /Lo que he hecho/ }).click();
        await expect(history.getByText('Evidencia enviada').first()).toBeVisible();
        await expect(history.getByText('Evidencia rechazada')).toHaveCount(0);
        await audit(page, ui);
    });

    test('sin actividad lo dice', async ({ page, ui }) => {
        await signIn(page, 'registro');
        await page.goto('/activity/mine?scope=about');

        await expect(page.getByRole('heading', { name: 'Todavía no hay actividad' })).toBeVisible();
        await audit(page, ui);
    });
});
