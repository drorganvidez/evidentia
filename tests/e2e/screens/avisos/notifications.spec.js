import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

/**
 * The inbox of the notices (database/seeders/E2e/NotificationsSeeder.php): the student reads hers, the coordinator
 * opens one and the secretary marks them all as read. The counts are read, never fixed, because other seeds notify
 * too; the writes only run on the desktop project and pass again on a seed that already ran them.
 */
async function audit(page, ui) {
    await ui.noOverflow();

    for (const theme of ['light', 'dark']) {
        await ui.setTheme(theme);
        expect(await ui.axe(), `axe en ${theme}`).toEqual([]);
    }
}

const bell = (page) => page.getByRole('link', { name: /^Avisos(, \d+ sin leer)?$/ }).first();

async function unread(page) {
    const name = (await bell(page).textContent()) ?? '';

    return Number(name.match(/(\d+) sin leer/)?.[1] ?? 0);
}

test.describe('Avisos', () => {
    test('la estudiante ve sus avisos y la campana cuenta los no leídos', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/notifications');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Avisos');
        expect(await unread(page)).toBeGreaterThan(0);

        const list = page.getByRole('list', { name: 'Lista de avisos' });
        await expect(
            // the list leaves a space before the hidden comma
            list.getByRole('link', { name: /^Evidencia rechazada\s?, sin leer$/ }),
        ).toBeVisible();
        await expect(list.getByText('Recogida de material · Faltan las fotos')).toBeVisible();
        await expect(
            list.getByRole('link', { name: 'Evidencia aceptada', exact: true }),
        ).toBeVisible();
        await expect(page.getByRole('link', { name: 'Preferencias', exact: true })).toBeVisible();
        await audit(page, ui);
    });

    test('la pestaña sin leer deja fuera los leídos', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/notifications?unread=1');

        await expect(
            page
                .getByRole('navigation', { name: 'Filtrar avisos' })
                .getByRole('link', { name: /Sin leer/ }),
        ).toHaveAttribute('aria-current', 'page');
        await expect(
            page.getByRole('link', { name: 'Evidencia aceptada', exact: true }),
        ).toHaveCount(0);
        await ui.noOverflow();
    });

    test.describe('marcas de lectura', () => {
        test.skip(({ isMobile }) => isMobile, 'Las mutaciones solo en escritorio');

        test('el coordinador abre un aviso y llega a la evidencia', async ({ page }) => {
            await signIn(page, 'coordinador');
            await page.goto('/notifications');
            const before = await unread(page);

            const fresh = page.getByRole('link', { name: /, sin leer$/ }).first();
            const link =
                (await fresh.count()) > 0
                    ? fresh
                    : page.getByRole('link', { name: /^Evidencia recibida/ }).first();
            const response = await page.request.get((await link.getAttribute('href')) ?? '', {
                maxRedirects: 0,
            });

            expect(response.status()).toBe(302);
            expect(new URL(response.headers().location).pathname).toMatch(
                /^\/coordinator\/evidence\/view\/\d+$/,
            );

            await page.reload();
            expect(await unread(page)).toBe(before > 0 ? before - 1 : 0);
        });

        test('la secretaria marca todo como leído', async ({ page }) => {
            await signIn(page, 'secretario');
            await page.goto('/notifications');
            const button = page.getByRole('button', { name: 'Marcar todo como leído' });

            if (await button.isEnabled()) {
                await button.click();
                await expect(
                    page
                        .getByRole('region', { name: 'Avisos' })
                        .getByText('Avisos marcados como leídos.'),
                ).toBeVisible();
            }

            await expect(bell(page)).toHaveAccessibleName('Avisos');
            await expect(button).toBeDisabled();
            await page.getByRole('link', { name: /Sin leer/ }).click();
            await expect(page.getByText('No tienes avisos')).toBeVisible();
        });
    });
});
