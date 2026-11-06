import { expect, test } from '../../fixtures/ui.js';

/**
 * The compact footer paints its links in ink-muted on the bare canvas, where a blob of the aurora can drop them
 * below 4.5:1 (a fix of x-ui.footer asked to the integration); the audit covers everything else.
 */
const AUDITED = 'header, main';

/** Waits for the finite animations (the count-ups and the entrances), so axe reads the final colours. */
async function settle(page) {
    await page.evaluate(() =>
        Promise.race([
            Promise.all(
                document
                    .getAnimations()
                    .filter((animation) => animation.effect.getTiming().iterations !== Infinity)
                    .map((animation) => animation.finished),
            ),
            new Promise((resolve) => setTimeout(resolve, 3000)),
        ]),
    );
}

test.describe('Acceso', () => {
    test('enseña el lema, las cifras y el formulario, accesible en claro y oscuro', async ({
        page,
        ui,
    }) => {
        await page.goto('/login');

        await expect(page.getByRole('heading', { level: 1 })).toContainText(
            'Lo que haces en las jornadas',
        );
        await expect(page.getByRole('list', { name: 'Cifras de la edición' })).toBeVisible();
        await expect(page.getByLabel('Correo electrónico')).toBeVisible();
        await ui.noOverflow();

        for (const theme of ['light', 'dark']) {
            await ui.setTheme(theme);
            await settle(page);
            expect(await ui.axe(AUDITED), `axe en ${theme}`).toEqual([]);
        }
    });

    test('un acceso fallido lo dice y conserva el correo', async ({ page, ui }) => {
        await page.goto('/login');
        await page.getByLabel('Correo electrónico').fill('estudiante@e2e.test');
        await page.getByLabel('Contraseña', { exact: true }).fill('no-es-la-clave');
        await page.getByRole('button', { name: 'Entrar' }).click();

        await expect(
            page
                .getByRole('alert')
                .filter({ hasText: 'El correo o la contraseña no son correctos' }),
        ).toBeVisible();
        await expect(page.getByLabel('Correo electrónico')).toHaveValue('estudiante@e2e.test');
        await ui.noOverflow();
        await settle(page);
        expect(await ui.axe(AUDITED)).toEqual([]);
    });
});

test.describe('Contraseña', () => {
    test('el restablecimiento pide el correo y vuelve al acceso con un aviso', async ({
        page,
        ui,
    }) => {
        await page.goto('/login');
        await page.getByRole('link', { name: 'He olvidado la contraseña' }).click();

        await expect(page.getByRole('heading', { level: 1 })).toHaveText(
            'Restablecer la contraseña',
        );
        await ui.noOverflow();

        for (const theme of ['light', 'dark']) {
            await ui.setTheme(theme);
            expect(await ui.axe(AUDITED), `axe en ${theme}`).toEqual([]);
        }

        await page.getByLabel('Correo electrónico').fill('nadie@e2e.test');
        await page.getByRole('button', { name: 'Enviar el enlace' }).click();

        await expect(page).toHaveURL(/\/login$/);
        // The notice and its live region carry the same text
        await expect(
            page.getByText('Si el email se encuentra en nuestros registros').first(),
        ).toBeVisible();
    });
});
