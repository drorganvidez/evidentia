import { signIn } from '../../fixtures/auth.js';
import { choose, expect } from '../../fixtures/ui.js';

/** Waits until the page stops moving: loaded, with its fonts swapped in. */
async function settled(page) {
    await page.waitForLoadState('load');
    await page.evaluate(() => document.fonts.ready.then(() => true));
}

/** Audits the page in the light and the dark theme and checks the reflow in both. */
export async function audit(page, ui) {
    await settled(page);
    expect(await ui.axe(), 'WCAG en claro').toEqual([]);
    await ui.noOverflow();
    await ui.setTheme('dark');
    await settled(page);
    expect(await ui.axe(), 'WCAG en oscuro').toEqual([]);
    await ui.noOverflow();
    await ui.setTheme('system');
}

/** A title unique to this run, to find the rows a flow creates among the seeded ones. */
export function unique(prefix) {
    return `${prefix} ${Date.now().toString(36)}`;
}

/** Signs another person in on the same page. */
export async function switchTo(page, who) {
    await page.context().clearCookies();
    await signIn(page, who);
}

/** The id of an evidence of the signed-in student, from the link of its row in «Mis evidencias». */
export async function evidenceId(page, title) {
    await page.goto('/evidence/list');
    const href = await page
        .getByRole('table', { name: 'Mis evidencias' })
        .getByRole('link', { name: title, exact: true })
        .getAttribute('href');

    return href.match(/(\d+)$/)[1];
}

/** Publishes a pending evidence of the student in Logística, with one proof, and returns its id. */
export async function publishEvidence(page, title) {
    await page.goto('/evidence/create');
    await page.getByLabel('Título').fill(title);
    await page.getByLabel('Horas').fill('1');
    await page.getByLabel('Minutos').fill('0');
    await choose(page.getByRole('combobox', { name: 'Comité', exact: true }), 'Logística');
    await page.getByLabel('Descripción').fill('Montaje y desmontaje del escenario principal.');
    await page.getByLabel('Arrastra aquí las pruebas o pulsa para elegirlas').setInputFiles({
        name: 'fotos.pdf',
        mimeType: 'application/pdf',
        buffer: Buffer.from('%PDF-1.4 fotos'),
    });
    await expect(page.getByText('Subido', { exact: true })).toBeVisible();
    await page.getByRole('button', { name: 'Publicar', exact: true }).click();
    await page.getByRole('alertdialog').getByRole('button', { name: 'Sí, publicar' }).click();
    await expect(page).toHaveURL(/\/evidence\/list$/);

    return evidenceId(page, title);
}

/** The notices region of the new layout. */
export function notices(page) {
    return page.getByRole('region', { name: 'Avisos' });
}
