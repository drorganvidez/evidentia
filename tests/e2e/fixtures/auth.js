/**
 * Signs a person of the end-to-end seed in through the real login (database/seeders/E2eSeeder.php): GET /login for
 * the token, POST the form with the page's own request context, so the session cookie lands in the browser.
 *
 *     import { signIn } from '../fixtures/auth.js';
 *     await signIn(page, 'coordinador');
 *
 * People → estudiante, companero, coordinador, coordinadora (a second coordinator of Logística), secretario, registro,
 * presidente, profesor.
 */
export const PASSWORD = 'evidentia';

export async function signIn(page, who) {
    const login = await page.request.get('/login');
    const html = await login.text();
    const token =
        html.match(/name="_token"\s+value="([^"]+)"/)?.[1] ??
        html.match(/name="csrf-token"\s+content="([^"]+)"/)?.[1];

    if (!token) {
        throw new Error('No se encontró el token CSRF del acceso');
    }

    const response = await page.request.post('/login', {
        form: { _token: token, email: `${who}@e2e.test`, password: PASSWORD },
        maxRedirects: 0,
    });

    if (response.status() !== 302 || response.headers().location?.includes('/login')) {
        throw new Error(`No se pudo iniciar sesión como ${who} (${response.status()})`);
    }
}
