import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// The server errors for the president too, under their own routes and with the permissions of the lecturer
test.describe('Errores del servidor de la presidencia', () => {
    test.beforeEach(async ({ page }) => {
        // A request of the API, not a navigation: the error page would fail the console check of the fixture
        await page.request.get('/_ui/demo/boom');
        await signIn(page, 'presidente');
    });

    test('el presidente ve los errores desde su menú y los abre', async ({ page, ui }) => {
        await page.goto('/president/errors');

        await expect(page.getByRole('heading', { level: 1, name: 'Errores' })).toBeVisible();
        const table = page.getByRole('region', { name: 'Errores del servidor' });
        const report = table.getByRole('link', { name: 'RuntimeException' }).first();
        await expect(report).toHaveAttribute('href', /\/president\/errors\/\d+$/);
        await ui.noOverflow();

        await report.click();
        await expect(page).toHaveURL(/\/president\/errors\/\d+$/);
        await expect(page.getByText('Error de demostración del catálogo').first()).toBeVisible();
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });

    test('el presidente marca un error como resuelto y lo reabre', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        await page.goto('/president/errors');
        await page
            .getByRole('region', { name: 'Errores del servidor' })
            .getByRole('link', { name: 'RuntimeException' })
            .first()
            .click();
        const notices = page.getByRole('region', { name: 'Avisos' });

        await page.getByRole('button', { name: 'Marcar como resuelto' }).click();
        await expect(page).toHaveURL(/\/president\/errors$/);
        await expect(notices.getByText('Error marcado como resuelto.')).toBeVisible();

        await page.goto('/president/errors?estado=resueltos');
        await page
            .getByRole('region', { name: 'Errores del servidor' })
            .getByRole('link', { name: 'RuntimeException' })
            .first()
            .click();
        await page.getByRole('button', { name: 'Reabrir' }).click();
        await expect(page).toHaveURL(/\/president\/errors\/\d+$/);
        await expect(notices.getByText('Error reabierto.')).toBeVisible();
    });

    for (const path of ['/president/errors', '/president/errors?estado=todos']) {
        test(`pasa axe en claro y oscuro en ${path}`, async ({ page, ui }) => {
            await page.goto(path);
            await expect(page.getByRole('heading', { level: 1, name: 'Errores' })).toBeVisible();
            expect(await ui.axe()).toEqual([]);

            await ui.setTheme('dark');
            expect(await ui.axe()).toEqual([]);
            await ui.noOverflow();
        });
    }
});
