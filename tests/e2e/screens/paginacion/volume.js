import { signIn } from '../../fixtures/auth.js';

/**
 * Rows enough to paginate, made through the forms of the app with a request context of their own, so the browser of
 * the test stays signed out. Idempotent: what a previous run made counts. The seed stays small (E2eSeeder); this
 * folder runs after the specs that read the lists of the seed, and the phone runs on a fresh seed.
 *
 *     test.beforeAll(async ({ playwright }, testInfo) => ensureEvidences(playwright, testInfo));
 */
const pad = (n) => String(n).padStart(2, '0');

async function as(playwright, testInfo, who, action) {
    const api = await playwright.request.newContext({ baseURL: testInfo.project.use.baseURL });

    try {
        await signIn({ request: api }, who);

        return await action(api);
    } finally {
        await api.dispose();
    }
}

async function tokenOf(api, path) {
    const html = await (await api.get(path)).text();
    const token = html.match(/name="_token"\s+value="([^"]+)"/)?.[1];

    if (!token) {
        throw new Error(`No se encontró el token CSRF en ${path}`);
    }

    return { html, token };
}

/** How many rows the search of a paginated list finds, from the count of its toolbar. */
async function found(api, path, label) {
    const html = await (await api.get(path)).text();

    return Number(html.match(new RegExp(`tabular-nums">(\\d+) ${label}s?<`))?.[1] ?? 0);
}

async function post(api, path, form) {
    const response = await api.post(path, { form, maxRedirects: 0 });

    if (response.status() !== 302) {
        throw new Error(`${path} respondió ${response.status()}`);
    }
}

/** 30 accepted evidences «Volumen NN» of the president in Comunicación, which has no coordination. */
export async function ensureEvidences(playwright, testInfo, count = 30) {
    testInfo.setTimeout(120_000);

    await as(playwright, testInfo, 'presidente', async (api) => {
        const made = await found(api, '/president/evidence/list?q=Volumen', 'evidencia');
        const { html, token } = await tokenOf(api, '/evidence/create');
        const committee = html.match(
            /<option value="(\d+)"[^>]*>\s*Comunicación\s*<\/option>/,
        )?.[1];

        for (let n = made + 1; n <= count; n += 1) {
            await post(api, '/evidence/publish', {
                _token: token,
                title: `Volumen ${pad(n)}`,
                description: '<p>Evidencia de volumen para paginar la lista.</p>',
                hours: '1',
                committee,
            });
        }
    });
}

/** 30 students «Volumen NN, Ana», whose surnames sort after the people of the seed. */
export async function ensureStudents(playwright, testInfo, count = 30) {
    testInfo.setTimeout(120_000);

    await as(playwright, testInfo, 'profesor', async (api) => {
        const made = await found(api, '/lecture/user/list?q=Volumen', 'usuario');
        const { token } = await tokenOf(api, '/lecture/user/list');

        for (let n = made + 1; n <= count; n += 1) {
            await post(api, '/management/user/new', {
                _token: token,
                name: 'Ana',
                surname: `Volumen ${pad(n)}`,
                email: `volumen${pad(n)}@e2e.test`,
                username: `volumen${pad(n)}`,
            });
        }
    });
}
