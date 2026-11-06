import { PASSWORD } from '../../fixtures/auth.js';
import { contrast, expect, test } from '../../fixtures/ui.js';

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

    test('la escena de la plataforma acompaña al lema sin llegar a los lectores de pantalla', async ({
        page,
        ui,
        isMobile,
    }) => {
        await page.goto('/login');

        const scene = page.locator('[data-scene="hero"]');

        await expect(scene).toBeVisible();
        await expect(scene).toHaveAttribute('aria-hidden', 'true');
        await expect(scene.locator('a, button, input, select, textarea, [tabindex]')).toHaveCount(
            0,
        );

        const tree = await page.locator('main').ariaSnapshot();

        expect(tree).toContain('Lo que haces en las jornadas');
        expect(tree).not.toContain('Taller de accesibilidad web');
        expect(tree).not.toContain('Mis horas');

        // The evidence with its status stays on the phone; the hours, the meeting, the bonus and the committees join it
        await expect(scene.getByText('Taller de accesibilidad web')).toBeVisible();
        await expect(scene.getByText('Aceptada')).toBeVisible();

        for (const piece of [
            'Mis horas',
            'Reunión ordinaria 3',
            'Montaje del stand',
            'Logística',
        ]) {
            await expect(scene.getByText(piece)).toBeVisible({ visible: !isMobile });
        }

        const box = await scene.boundingBox();
        const card = await page.getByRole('heading', { name: 'Iniciar sesión' }).boundingBox();

        if (isMobile) {
            expect(box.height, 'Compacta en el teléfono').toBeLessThan(160);
            expect(card.y, 'El formulario va debajo').toBeGreaterThan(box.y + box.height);
        } else {
            expect(box.x + box.width, 'A la izquierda del formulario').toBeLessThanOrEqual(card.x);
        }

        await ui.noOverflow();
    });

    for (const theme of ['light', 'dark']) {
        test(`los textos de la escena superan 4,5 a 1 sobre su fondo en tema ${theme === 'dark' ? 'oscuro' : 'claro'}`, async ({
            page,
            ui,
        }) => {
            await page.goto('/login');
            await ui.setTheme(theme);
            await settle(page);

            // Every visible element of the scene with text of its own, measured against what lies under it
            const texts = page
                .locator('[data-scene="hero"]')
                .locator('xpath=.//*[text()[normalize-space()]]')
                .filter({ visible: true });
            const count = await texts.count();

            expect(count, 'Textos medidos').toBeGreaterThan(3);

            for (let index = 0; index < count; index++) {
                const text = texts.nth(index);
                const paint = await ui.paint(text);

                expect(
                    contrast(paint.text, paint.fill),
                    `«${(await text.textContent()).trim()}»`,
                ).toBeGreaterThanOrEqual(4.5);
            }
        });
    }

    test('la estudiante entra y llega al inicio', async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel('Correo electrónico').fill('estudiante@e2e.test');
        await page.getByLabel('Contraseña', { exact: true }).fill(PASSWORD);
        await page.getByRole('button', { name: 'Entrar' }).click();

        await expect(page).toHaveURL(/\/$/);
        await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
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
