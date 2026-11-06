import { signIn } from './fixtures/auth.js';
import { expect, test } from './fixtures/ui.js';

// The harness of the screens by role: every person of the seed reaches the home page signed in
for (const who of [
    'estudiante',
    'coordinador',
    'secretario',
    'registro',
    'presidente',
    'profesor',
]) {
    test(`${who} inicia sesión con el arnés`, async ({ page }) => {
        await signIn(page, who);

        const response = await page.request.get('/');

        expect(response.status()).toBe(200);
        expect(response.url()).not.toContain('/login');
    });
}
