import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// Whatever goes wrong in a list of the student, one click opens the message to who answers for it
test.describe('Contacto rápido del alumno', () => {
    test.beforeEach(async ({ page }) => {
        await signIn(page, 'estudiante');
    });

    test('desde sus evidencias escribe a la coordinación', async ({ page, ui }) => {
        await page.goto('/evidence/list');
        await ui.noOverflow();
        await page.getByRole('link', { name: 'Escribir a la coordinación' }).click();

        await expect(page.getByLabel('Destinatario')).toHaveValue(/^COORDINATOR:\d+$/);
        await expect(page.getByLabel('Asunto')).toHaveValue('Sobre mis evidencias');
    });

    test('desde sus reuniones escribe a la secretaría de una de ellas', async ({ page, ui }) => {
        await page.goto('/meeting/list');
        await ui.noOverflow();
        expect(await ui.axe()).toEqual([]);
        await page
            .getByRole('link', { name: 'Escribir a la secretaría sobre Reunión de logística' })
            .click();

        await expect(page.getByLabel('Destinatario')).toHaveValue(/^SECRETARY:\d+$/);
        await expect(page.getByLabel('Asunto')).toHaveValue('Sobre «Reunión de logística»');
    });

    test('desde sus asistencias escribe a la coordinación de registro', async ({ page, ui }) => {
        await page.goto('/attendee/list');
        await ui.noOverflow();
        expect(await ui.axe()).toEqual([]);
        await page
            .getByRole('link', {
                name: 'Escribir a la coordinación de registro sobre Taller de Git',
            })
            .click();

        await expect(page.getByLabel('Destinatario')).toHaveValue('REGISTER_COORDINATOR');
        await expect(page.getByLabel('Asunto')).toHaveValue(
            'Sobre mi asistencia a «Taller de Git»',
        );
    });
});
