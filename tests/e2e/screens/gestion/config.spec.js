import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// The settings of the course: the event, one card per phase with its day, its hour and whether it is open, the hour caps
test.describe('Configurar curso', () => {
    test.beforeEach(async ({ page }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/config');
    });

    test('enseña cada plazo con su estado', async ({ page, ui }) => {
        await expect(
            page.getByRole('heading', { level: 1, name: 'Configurar curso' }),
        ).toBeVisible();
        const upload = page.getByRole('group', { name: 'Subida de evidencias' });
        await expect(upload.getByLabel('Día')).not.toHaveValue('');
        await expect(upload.getByLabel('Hora')).not.toHaveValue('');
        await expect(
            page.getByRole('region', { name: 'Validación de evidencias' }).getByText('Abierto'),
        ).toBeVisible();
        await ui.noOverflow();
    });

    test('ofrece cerrar ahora cada plazo abierto y pide confirmarlo', async ({ page, ui }) => {
        const card = page.getByRole('region', { name: 'Cerrar un plazo ahora' });
        await card.getByRole('button', { name: 'Subida de evidencias' }).click();

        const dialog = page.getByRole('alertdialog', { name: 'Cerrar subida de evidencias' });
        await expect(dialog.getByRole('button', { name: 'Sí, cerrar ahora' })).toBeVisible();
        expect(await ui.axe()).toEqual([]);
        await dialog.getByRole('button', { name: 'Cancelar' }).click();
        await expect(dialog).toBeHidden();
        await ui.noOverflow();
    });

    test('pasa axe en claro y oscuro', async ({ ui }) => {
        expect(await ui.axe()).toEqual([]);

        await ui.setTheme('dark');
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });

    test('avisa de un plazo de validación anterior al de subida y guarda', async ({
        page,
    }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        const validate = page.getByRole('group', { name: 'Validación de evidencias' });
        const original = await validate.getByLabel('Día').inputValue();
        await validate.getByLabel('Día').fill('2000-01-01');
        await page.getByRole('button', { name: 'Guardar configuración' }).click();

        await expect(page.getByText('Revisa el formulario')).toBeVisible();
        await expect(validate.getByLabel('Día')).toHaveValue('2000-01-01');

        await validate.getByLabel('Día').fill(original);
        await page.getByRole('button', { name: 'Guardar configuración' }).click();
        await expect(
            page
                .getByRole('region', { name: 'Avisos' })
                .getByText('Configuración guardada con éxito.'),
        ).toBeVisible();
        await expect(validate.getByLabel('Día')).toHaveValue(original);
    });

    test('guarda y quita los límites de horas', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        const limits = page.getByRole('region', { name: 'Límites de horas' });
        await limits.getByLabel('Evidencias').fill('30');
        await limits.getByLabel('Asistencia').fill('7.5');
        await page.getByRole('button', { name: 'Guardar configuración' }).click();
        await expect(
            page
                .getByRole('region', { name: 'Avisos' })
                .getByText('Configuración guardada con éxito.'),
        ).toBeVisible();
        await expect(limits.getByLabel('Evidencias')).toHaveValue('30');
        await expect(limits.getByLabel('Asistencia')).toHaveValue('7.5');

        await limits.getByLabel('Evidencias').fill('');
        await limits.getByLabel('Asistencia').fill('');
        await page.getByRole('button', { name: 'Guardar configuración' }).click();
        await expect(
            page
                .getByRole('region', { name: 'Avisos' })
                .getByText('Configuración guardada con éxito.'),
        ).toBeVisible();
        await expect(limits.getByLabel('Evidencias')).toHaveValue('');
    });

    test('renombra el evento y el acceso lo muestra', async ({ page, browser }, testInfo) => {
        test.skip(testInfo.project.name !== 'chromium', 'Las mutaciones solo en escritorio');

        const event = page.getByRole('region', { name: 'Evento' });
        await expect(event.getByLabel('Nombre del evento')).toHaveValue('Jornadas InnoSoft Days');
        await expect(event.getByLabel('Edición')).toHaveValue('2026');

        const save = async (name, edition) => {
            await event.getByLabel('Nombre del evento').fill(name);
            await event.getByLabel('Edición').fill(edition);
            await page.getByRole('button', { name: 'Guardar configuración' }).click();
            await expect(
                page
                    .getByRole('region', { name: 'Avisos' })
                    .getByText('Configuración guardada con éxito.'),
            ).toBeVisible();
        };

        await save('Semana de la Ciencia', '2027-28');
        await expect(event.getByLabel('Nombre del evento')).toHaveValue('Semana de la Ciencia');

        const guest = await browser.newPage();
        await guest.goto('/login');
        await expect(guest.getByText('Semana de la Ciencia').first()).toBeVisible();
        await expect(guest.getByText('Edición 2027-28')).toBeVisible();
        await guest.close();

        await save('Jornadas InnoSoft Days', '2026');
    });
});
