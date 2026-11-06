import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import { auditThemes, confirmRemoval, mutatesData, notice, unique } from './helpers.js';

test.describe('Actas', () => {
    // Two themes, two audits and a reload per screen
    test.slow();

    test.beforeEach(async ({ page }) => {
        await signIn(page, 'secretario');
    });

    test('los tres pasos cumplen WCAG 2.2 AA y vuelcan la convocatoria y la hoja', async ({
        page,
        ui,
    }) => {
        await page.goto('/secretary/meeting/manage/minutes/create');
        const steps = page.getByRole('list', { name: 'Pasos del acta' });
        await expect(steps).toBeVisible();
        await auditThemes(page, ui);

        await page.getByLabel('Convocatoria').selectOption({ label: 'Convocatoria de logística' });
        await page.getByRole('button', { name: 'Siguiente' }).click();
        await expect(page.getByText('Hoja de firmas de logística')).toBeVisible();
        await auditThemes(page, ui);

        await page.getByRole('button', { name: 'Siguiente' }).click();
        await expect(page.getByLabel('Título', { exact: true }).first()).toHaveValue(
            'Convocatoria de logística',
        );
        await expect(page.getByLabel('Lugar')).toHaveValue('Aula A3.10');
        await expect(
            page.getByRole('list', { name: 'Seleccionados' }).getByRole('listitem'),
        ).toHaveCount(1);
        await expect(page.locator('[data-point]')).toHaveCount(1);
        await auditThemes(page, ui);
    });

    test('la edición cumple WCAG 2.2 AA en claro y oscuro', async ({ page, ui }) => {
        await page.goto('/secretary/meeting/manage/minutes/list');
        await page.getByRole('link', { name: 'Reunión de logística', exact: true }).click();

        await expect(
            page.getByRole('heading', { level: 1, name: 'Reunión de logística' }),
        ).toBeVisible();
        await expect(page.getByLabel('Horas')).toHaveValue('2');
        await expect(page.locator('[data-point]')).toHaveCount(1);
        await auditThemes(page, ui);
    });

    test.describe('flujo completo', () => {
        mutatesData(test);

        test('redacta un acta con una lista predeterminada, la edita y la elimina', async ({
            page,
        }) => {
            const title = unique('Reunión de prueba');

            await page.goto('/secretary/meeting/manage/minutes/create/step1');
            await page.getByRole('button', { name: 'Siguiente' }).click();
            await page.getByRole('button', { name: 'Siguiente' }).click();

            await page.getByLabel('Título', { exact: true }).first().fill(title);
            await page.getByLabel('Fecha').fill('2026-10-16');
            await page.getByLabel('Hora', { exact: true }).fill('16:30');
            await page.getByLabel('Lugar').fill('Sala de juntas');
            await page.getByLabel('Horas').fill('1');
            await page.getByLabel('Minutos').fill('30');

            await page.getByLabel('Lista predeterminada').selectOption({ index: 1 });
            await page.getByRole('button', { name: 'Cargar' }).click();
            await page.getByRole('button', { name: 'Sustituir' }).click();
            await expect(
                page.getByRole('list', { name: 'Seleccionados' }).getByRole('listitem'),
            ).toHaveCount(2);

            await page.getByRole('button', { name: 'Añadir punto' }).click();
            const point = page.locator('[data-point]').first();
            await point.locator('[data-field="title"]').fill('Balance de las jornadas');
            await point.locator('[data-field="duration"]').fill('15');
            await point.getByRole('button', { name: 'Añadir acuerdo' }).click();
            await point.locator('[data-field="agreement"]').fill('Enviar la memoria');
            await page.getByRole('button', { name: 'Crear acta' }).click();

            await expect(notice(page, 'Acta de reunión creada con éxito.')).toBeVisible();
            const row = page.getByRole('row').filter({ hasText: title });
            await expect(row).toContainText('1,50 h');

            const pdf = await page.request.get(
                await row
                    .getByRole('link', { name: `Descargar el PDF de ${title}` })
                    .getAttribute('href'),
            );
            expect(pdf.status()).toBe(200);
            expect((await pdf.body()).subarray(0, 4).toString()).toBe('%PDF');

            await row.getByRole('link', { name: title, exact: true }).click();
            await expect(page.locator('[data-field="agreement"]')).toHaveValue('Enviar la memoria');
            await page.getByLabel('Horas').fill('2');
            await page.getByLabel('Minutos').fill('0');
            await page.getByRole('button', { name: 'Guardar acta' }).click();
            await expect(notice(page, 'Acta de reunión editada con éxito.')).toBeVisible();
            await expect(page.getByRole('row').filter({ hasText: title })).toContainText('2,00 h');

            await page.getByRole('link', { name: title, exact: true }).click();
            await confirmRemoval(
                page,
                page.getByRole('button', { name: 'Eliminar', exact: true }),
                'Eliminar el acta',
                'Acta de reunión eliminada con éxito.',
            );
            await expect(page.getByRole('row').filter({ hasText: title })).toHaveCount(0);
        });
    });
});
