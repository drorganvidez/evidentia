import { expect, test } from '../fixtures/ui.js';

// dompdf renders this document in the tests of PHPUnit; here the browser opens it under the strict policy
test.describe('Acta en PDF', () => {
    test('el acta de ejemplo se abre en el navegador con sus estilos y sin errores', async ({
        page,
        ui,
    }) => {
        const response = await page.goto('/_ui/pdf');

        expect(response.status()).toBe(200);
        expect(response.headers()['content-security-policy']).toContain("style-src 'self' 'nonce-");

        await expect(page).toHaveTitle(/^Acta de reunión · Jornadas InnoSoft Days \d{4}$/);
        await expect(
            page.getByRole('heading', { level: 1, name: 'Acta de reunión' }),
        ).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Desarrollo de la reunión' })).toBeVisible();
        await expect(page.locator('table.pdf-table')).toHaveCount(4);

        // The embedded stylesheet carries the nonce of the request, so the policy lets it apply
        expect(
            await page
                .getByRole('heading', { level: 1 })
                .evaluate((heading) => getComputedStyle(heading).textTransform),
        ).toBe('uppercase');

        // No reflow check: the document has the width of the paper and no viewport, so a phone shows it zoomed out
        expect(await ui.axe()).toEqual([]);
    });
});
