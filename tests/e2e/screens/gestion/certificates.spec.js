import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

async function audit(page, ui) {
    await ui.noOverflow();

    for (const theme of ['light', 'dark']) {
        await ui.setTheme(theme);
        expect(await ui.axe(), `axe en ${theme}`).toEqual([]);
    }
}

// Certificates of attendance, diplomas of the management and the public page that verifies their code
test.describe('Certificados y diplomas', () => {
    test('el estudiante descarga el certificado de un evento al que asistió', async ({ page }) => {
        await signIn(page, 'estudiante');
        await page.goto('/attendee/list');

        const download = page.waitForEvent('download');
        await page.getByRole('link', { name: 'Descargar el certificado de Taller de Git' }).click();
        expect((await download).suggestedFilename()).toMatch(/^certificado-de-asistencia-.*\.pdf$/);
    });

    test('la gestión ve la lista y el formulario', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/certificates');
        await expect(
            page.getByRole('heading', { level: 1, name: 'Certificados y diplomas' }),
        ).toBeVisible();
        await audit(page, ui);

        await page.goto('/certificates/create');
        await expect(page.getByRole('heading', { level: 1, name: 'Emitir diploma' })).toBeVisible();
        await audit(page, ui);
    });

    test('la verificación pública avisa de un código que no existe', async ({ page, ui }) => {
        await page.goto('/certificados/verificar');
        await page.getByLabel('Código de verificación').fill('AAAA-BBBB-CCCC');
        await page.getByRole('button', { name: 'Verificar' }).click();
        await expect(page.getByText('Código no válido')).toBeVisible();
        await audit(page, ui);
    });

    test('emite un diploma, lo verifica y lo revoca', async ({ page, ui }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        const notices = page.getByRole('region', { name: 'Avisos' });
        const name = `Ada Ponente ${Date.now()}`;
        await signIn(page, 'presidente');
        await page.goto('/certificates');
        await page.locator('#certificate-create').click();
        await page.getByRole('button', { name: 'Emitir diploma' }).click();
        await expect(page.getByText('Revisa el formulario')).toBeVisible();

        await page.getByLabel('Nombre', { exact: true }).fill(name);
        await page.getByLabel('Motivo', { exact: true }).fill('impartir la charla de apertura');
        await page.getByLabel('Horas', { exact: true }).fill('2');
        await page.getByRole('button', { name: 'Emitir diploma' }).click();
        const notice = notices.getByText(`Diploma emitido para ${name}`);
        await expect(notice).toBeVisible();
        const code = (await notice.textContent()).match(/[2-9A-Z]{4}-[2-9A-Z]{4}-[2-9A-Z]{4}/)[0];
        const table = page.getByRole('region', { name: 'Certificados emitidos' });

        await page.goto(`/certificados/verificar/${code}`);
        await expect(page.getByText('Certificado válido')).toBeVisible();
        await expect(page.getByText(name)).toBeVisible();
        await audit(page, ui);

        await page.goto(`/certificates?q=${encodeURIComponent(name)}`);
        await table.getByRole('button', { name: `Revocar ${code}` }).click();
        await page
            .getByRole('alertdialog', { name: 'Revocar el certificado' })
            .getByRole('button', { name: 'Sí, revocar' })
            .click();
        await expect(notices.getByText(`Certificado ${code} revocado.`)).toBeVisible();

        await page.goto(`/certificados/verificar/${code}`);
        await expect(page.getByText('Código no válido')).toBeVisible();
    });
});
