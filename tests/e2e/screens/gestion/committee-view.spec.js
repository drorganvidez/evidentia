import { signIn } from '../../fixtures/auth.js';
import { choose, expect, test } from '../../fixtures/ui.js';

// What the management sees of each committee: its page with alerts and nudges, and the filtered lists
test.describe('Ficha de un comité', () => {
    test('el presidente llega desde el listado y ve todo lo del comité', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/committee/list');

        const list = page.getByRole('region', { name: 'Comités del curso' });
        await expect(list.getByText('3 alertas')).toBeVisible();
        await list.getByRole('link', { name: 'Logística' }).click();

        await expect(page.getByRole('heading', { level: 1, name: 'Logística' })).toBeVisible();
        await expect(
            page.getByRole('region', { name: 'Alertas' }).getByText('Todo en orden'),
        ).toBeVisible();
        const team = page.getByRole('list', { name: 'Equipo de Logística' });
        await expect(team.getByText('Carlos Navarro Ruiz')).toBeVisible();
        await expect(team.getByText('Marta Ruiz Díaz')).toBeVisible();
        await expect(page.getByRole('region', { name: 'Miembros de Logística' })).toBeVisible();
        await expect(page.getByRole('region', { name: 'Mensajes al comité' })).toContainText(
            'Cambio de turno en el registro',
        );
        await ui.noOverflow();
    });

    test('el profesor ve las alertas de un comité sin responsables', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/committee/list');
        await page
            .getByRole('region', { name: 'Comités del curso' })
            .getByRole('link', { name: 'Comunicación' })
            .click();

        const alerts = page.getByRole('list', { name: 'Alertas de Comunicación' });
        await expect(alerts.getByText('Sin coordinación')).toBeVisible();
        await expect(alerts.getByText('Sin reuniones todavía')).toBeVisible();
        await expect(
            alerts.getByRole('link', { name: 'Asignar en Alumnos' }).first(),
        ).toBeVisible();
        await ui.noOverflow();
    });

    test('el toque abre el mensaje a la coordinación ya escrito', async ({ page }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/committee/list');
        await page
            .getByRole('region', { name: 'Comités del curso' })
            .getByRole('link', { name: 'Logística' })
            .click();
        await page.getByRole('link', { name: 'Escribir a la coordinación' }).click();

        await expect(page.getByRole('combobox', { name: 'Destinatario' })).toHaveValue(
            'Coordinación de Logística',
        );
        await expect(page.getByLabel('Asunto')).toHaveValue('Logística');
    });

    test('pasa axe en claro y oscuro', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/committee/list');
        expect(await ui.axe()).toEqual([]);
        await page
            .getByRole('region', { name: 'Comités del curso' })
            .getByRole('link', { name: 'Logística' })
            .click();
        await expect(page.getByRole('heading', { level: 1, name: 'Logística' })).toBeVisible();
        expect(await ui.axe()).toEqual([]);

        await ui.setTheme('dark');
        expect(await ui.axe()).toEqual([]);
        await ui.noOverflow();
    });
});

test.describe('Filtros de gestión', () => {
    test('el profesor filtra las evidencias por comité y conserva el filtro en las pestañas', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/evidence/list');

        await page.getByText('Filtros de evidencias').click();
        await choose(page.getByRole('combobox', { name: 'Comité' }), 'Comunicación');
        await page.getByRole('button', { name: 'Aplicar' }).click();
        await expect(page).toHaveURL(/comite=\d+/);
        await expect(page.getByText('No hay evidencias')).toBeVisible();

        await page.getByRole('link', { name: /Atrasadas/ }).click();
        await expect(page).toHaveURL(/estado=atrasadas/);
        await expect(page).toHaveURL(/comite=\d+/);
        await ui.noOverflow();
        expect(await ui.axe()).toEqual([]);
    });

    test('el presidente filtra las reuniones sin acta', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/president/meeting/list?acta=sin');

        await expect(page.getByText('No hay reuniones')).toBeVisible();
        await page.goto('/president/meeting/list?acta=con');
        await expect(page.getByText('Reunión de logística')).toBeVisible();
        await ui.noOverflow();
        expect(await ui.axe()).toEqual([]);
    });
});
