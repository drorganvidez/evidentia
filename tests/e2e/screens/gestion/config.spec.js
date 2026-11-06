import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';

// The deadlines of the course: one card per phase with its day, its hour and whether it is open
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
});
