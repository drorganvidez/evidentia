import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import {
    alpineReady,
    auditThemes,
    confirmRemoval,
    expectNotice,
    mutatesData,
    unique,
} from './helpers.js';

test.describe('Convocatorias', () => {
    // Two themes, two audits and a reload per screen
    test.slow();

    test.beforeEach(async ({ page }) => {
        await signIn(page, 'secretario');
    });

    test('el formulario nuevo cumple WCAG 2.2 AA en claro y oscuro', async ({ page, ui }) => {
        await page.goto('/secretary/meeting/manage/request/create');

        await expect(
            page.getByRole('heading', { level: 1, name: 'Nueva convocatoria' }),
        ).toBeVisible();
        await expect(page.getByRole('switch', { name: 'Crear su hoja de firmas' })).toBeChecked();
        await expect(page.getByRole('radio', { name: 'Ordinaria', exact: true })).toBeChecked();
        await auditThemes(page, ui);
    });

    test('la edición carga la convocatoria y su orden del día', async ({ page, ui }) => {
        await page.goto('/secretary/meeting/manage/request/list');
        await page.getByRole('link', { name: 'Convocatoria de logística', exact: true }).click();

        await expect(
            page.getByRole('heading', { level: 1, name: 'Convocatoria de logística' }),
        ).toBeVisible();
        await expect(page.getByLabel('Lugar')).toHaveValue('Aula A3.10');
        await expect(page.getByRole('textbox', { name: 'Punto 1', exact: true })).toHaveValue(
            'Reparto de tareas',
        );
        await expect(page.getByRole('link', { name: 'Descargar PDF' })).toBeVisible();
        await auditThemes(page, ui);
    });

    test.describe('flujo completo', () => {
        mutatesData(test);

        test('crea con orden del día, edita, descarga y elimina una convocatoria', async ({
            page,
        }) => {
            const title = unique('Convocatoria de prueba');

            await page.goto('/secretary/meeting/manage/request/create');
            await alpineReady(page);
            await page.getByLabel('Título', { exact: true }).fill(title);
            await page.getByLabel('Fecha').fill('2030-03-14');
            await page.getByLabel('Hora', { exact: true }).fill('17:30');
            await page.getByLabel('Lugar').fill('Sala de juntas');
            await page.getByText('Extraordinaria').click();
            await page.getByText('Híbrida').click();

            const draft = page.getByRole('textbox', { name: 'Nuevo punto' });
            await draft.fill('Turno de preguntas');
            await draft.press('Enter');
            await draft.fill('Bienvenida');
            await draft.press('Enter');
            await page.getByRole('button', { name: /^Subir el punto 2/ }).click();
            await expect(page.locator('input[name="points_list"]')).toHaveValue(
                JSON.stringify(['Bienvenida', 'Turno de preguntas']),
            );
            await page.getByRole('switch', { name: 'Crear su hoja de firmas' }).uncheck();
            await page.getByRole('button', { name: 'Crear convocatoria' }).click();

            await expectNotice(page, 'Convocatoria de reunión creada con éxito.');
            const row = page.getByRole('row').filter({ hasText: title });
            await expect(row).toContainText('Sin hoja');

            const pdf = await page.request.get(
                await row
                    .getByRole('link', { name: `Descargar el PDF de ${title}` })
                    .getAttribute('href'),
            );
            expect(pdf.status()).toBe(200);
            expect((await pdf.body()).subarray(0, 4).toString()).toBe('%PDF');

            await row.getByRole('link', { name: title, exact: true }).click();
            await expect(page.getByRole('radio', { name: 'Extraordinaria' })).toBeChecked();
            await expect(page.getByRole('radio', { name: 'Híbrida' })).toBeChecked();
            await expect(page.getByRole('textbox', { name: 'Punto 1', exact: true })).toHaveValue(
                'Bienvenida',
            );
            await page.getByLabel('Título', { exact: true }).fill(`${title} editada`);
            await page.getByRole('button', { name: 'Guardar convocatoria' }).click();
            await expectNotice(page, 'Convocatoria de reunión editada con éxito.');

            await page.getByRole('link', { name: `${title} editada`, exact: true }).click();
            await confirmRemoval(
                page,
                page.getByRole('button', { name: 'Eliminar', exact: true }),
                'Eliminar la convocatoria',
                'Convocatoria eliminada con éxito.',
            );
            await expect(page.getByRole('row').filter({ hasText: title })).toHaveCount(0);
        });

        test('un orden del día vacío vuelve con el aviso y conserva los datos', async ({
            page,
        }) => {
            await page.goto('/secretary/meeting/manage/request/create');
            await page.getByLabel('Título', { exact: true }).fill('Sin fecha');
            await page.getByRole('button', { name: 'Crear convocatoria' }).click();

            await expectNotice(page, 'Hay errores en el formulario.');
            await expect(page.getByRole('group', { name: 'Revisa el formulario' })).toBeFocused();
            await expect(page.getByLabel('Título', { exact: true })).toHaveValue('Sin fecha');
        });
    });
});
