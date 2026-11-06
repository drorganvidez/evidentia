import { signIn } from '../../fixtures/auth.js';
import { choose, expect, test } from '../../fixtures/ui.js';
import { audit, evidenceId, notices, switchTo, unique } from './helpers.js';

// The inbox of the people in charge, «Mis mensajes», the form of a new message and the thread
test.describe('Mensajes', () => {
    // several pages and two audits of axe per test, slow on the emulated phone
    test.slow();

    test('el coordinador lee la bandeja de su comité', async ({ page, ui }) => {
        await signIn(page, 'coordinador');
        await page.goto('/messages');

        await expect(page.getByRole('heading', { level: 1, name: 'Mensajes' })).toBeVisible();
        const table = page.getByRole('table', { name: 'Mensajes recibidos' });
        await expect(
            table.getByRole('link', { name: 'Cambio de turno en el registro', exact: true }),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Duda sobre el photocall', exact: true }),
        ).toBeVisible();
        await expect(table.getByText('Propuesta para el cierre')).toHaveCount(0);

        const tabs = page.getByRole('navigation', { name: 'Filtrar por estado' });
        await tabs.getByRole('link', { name: /Todos/ }).click();
        await expect(page).toHaveURL(/state=all/);
        await expect(tabs.getByRole('link', { name: /Todos/ })).toHaveAttribute(
            'aria-current',
            'page',
        );
        await audit(page, ui);
    });

    test('el profesorado puede leer todos los hilos', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        await page.goto('/messages?to=all&state=all');

        const table = page.getByRole('table', { name: 'Mensajes recibidos' });
        await expect(
            table.getByRole('link', { name: 'Propuesta para el cierre', exact: true }),
        ).toBeVisible();
        await expect(
            table.getByRole('link', { name: 'Cambio de turno en el registro', exact: true }),
        ).toBeVisible();
        await expect(page.locator('select[name="to"]')).toHaveValue('all');
        await audit(page, ui);
    });

    test('el presidente lee todos los hilos en solo lectura', async ({ page, ui }) => {
        await signIn(page, 'presidente');
        await page.goto('/messages?to=all&state=all');

        const table = page.getByRole('table', { name: 'Mensajes recibidos' });
        await expect(page.locator('select[name="to"]')).toHaveValue('all');
        await table
            .getByRole('link', { name: 'Cambio de turno en el registro', exact: true })
            .click();

        await expect(
            page.getByRole('heading', { level: 1, name: 'Cambio de turno en el registro' }),
        ).toBeVisible();
        await expect(page.getByText('Solo lectura')).toBeVisible();
        await expect(page.getByLabel('Tu respuesta')).toHaveCount(0);
        await expect(page.getByRole('button', { name: 'Marcar como resuelto' })).toHaveCount(0);
        await audit(page, ui);
    });

    test('la estudiante ve sus hilos y la respuesta sin leer', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        await page.goto('/messages/mine');

        await expect(page.getByRole('heading', { level: 1, name: 'Mis mensajes' })).toBeVisible();
        const table = page.getByRole('table', { name: 'Mensajes que has escrito' });
        await expect(
            table.getByRole('link', { name: 'Duda sobre el photocall', exact: true }),
        ).toBeVisible();
        await audit(page, ui);
    });

    test('sin hilos propios lo dice y ofrece escribir', async ({ page, ui }) => {
        await signIn(page, 'secretario');
        await page.goto('/messages/mine');

        await expect(
            page.getByRole('heading', { name: 'Todavía no has escrito ningún mensaje' }),
        ).toBeVisible();
        await expect(
            page.getByRole('main').getByRole('link', { name: 'Escribir un mensaje' }).last(),
        ).toHaveAttribute('href', /\/messages\/create$/);
        await audit(page, ui);
    });

    test('el formulario sobre una evidencia propone su coordinación y valida en español', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'estudiante');
        const id = await evidenceId(page, 'Recogida de material');
        await page.goto(`/messages/create?evidence=${id}`);

        await expect(page.getByRole('heading', { level: 1, name: 'Nuevo mensaje' })).toBeVisible();
        await expect(page.getByRole('link', { name: /Sobre/ })).toContainText(
            'Recogida de material',
        );
        await expect(page.getByRole('combobox', { name: 'Destinatario' })).toHaveValue(
            'Coordinación de Logística',
        );
        await audit(page, ui);

        await page.getByLabel('Asunto').fill('');
        await page.getByRole('button', { name: 'Enviar' }).click();
        const summary = page.getByRole('group', { name: 'Revisa el formulario' });
        await expect(summary).toContainText('El campo asunto es obligatorio.');
        await expect(summary).toBeFocused();
    });

    test('el buzón de sugerencias abre un mensaje al profesorado', async ({ page }) => {
        await signIn(page, 'estudiante');
        await page.goto('/suggestionsmailbox');

        await expect(page).toHaveURL(/\/messages\/create\?to=LECTURE$/);
        await expect(
            page.getByRole('heading', { level: 1, name: 'Sugerencias al profesorado' }),
        ).toBeVisible();
        await expect(page.getByRole('combobox', { name: 'Destinatario' })).toHaveValue(
            'Profesorado',
        );
    });

    test('el hilo enseña los mensajes, los participantes y su historial', async ({ page, ui }) => {
        await signIn(page, 'coordinador');
        await page.goto('/messages?state=all');
        await page
            .getByRole('table', { name: 'Mensajes recibidos' })
            .getByRole('link', { name: 'Duda sobre el photocall', exact: true })
            .click();

        await expect(
            page.getByRole('heading', { level: 1, name: 'Duda sobre el photocall' }),
        ).toBeVisible();
        await expect(page.getByRole('article', { name: /Mensaje de Lucía/ })).toContainText(
            'desmontaje del photocall',
        );
        await expect(page.getByRole('region', { name: 'Participantes' })).toContainText('Carlos');
        await expect(page.getByRole('list', { name: 'Historial del mensaje' })).toContainText(
            'Mensaje enviado',
        );
        await audit(page, ui);
    });

    test.describe('conversación', () => {
        test.skip(({ isMobile }) => isMobile, 'Cambia datos');

        test('la estudiante escribe, el coordinador responde y lo resuelve, y ella lo reabre', async ({
            page,
        }) => {
            const subject = unique('Turno del sábado');

            await signIn(page, 'estudiante');
            await page.goto('/messages/create');
            await choose(
                page.getByRole('combobox', { name: 'Destinatario' }),
                'Coordinación de Logística',
            );
            await page.getByLabel('Asunto').fill(subject);
            await page
                .getByLabel('Mensaje', { exact: true })
                .fill('¿Puedo cambiar mi turno del sábado por el del domingo?');
            await page.getByRole('button', { name: 'Enviar' }).click();

            await expect(page.getByRole('heading', { level: 1, name: subject })).toBeVisible();
            await expect(notices(page).getByText('Mensaje enviado.')).toBeVisible();
            await expect(page.getByRole('button', { name: 'Marcar como resuelto' })).toHaveCount(0);
            const thread = page.url();

            await switchTo(page, 'coordinador');
            await page.goto('/messages');
            const row = page
                .getByRole('table', { name: 'Mensajes recibidos' })
                .getByRole('row')
                .filter({ hasText: subject });
            await expect(row.getByText('Sin leer.')).toHaveCount(1);
            await row.getByRole('link', { name: subject, exact: true }).click();
            await page.getByLabel('Tu respuesta').fill('Sí, queda cambiado.');
            await page.getByRole('button', { name: 'Enviar' }).click();
            await expect(notices(page).getByText('Respuesta enviada.')).toBeVisible();
            await expect(page.getByRole('article', { name: /Mensaje de Carlos/ })).toContainText(
                'Sí, queda cambiado.',
            );

            await page.getByRole('button', { name: 'Marcar como resuelto' }).click();
            await expect(notices(page).getByText('Hilo marcado como resuelto.')).toBeVisible();
            await expect(page.getByLabel('Tu respuesta')).toHaveCount(0);
            await expect(page.getByRole('list', { name: 'Historial del mensaje' })).toContainText(
                'Hilo resuelto',
            );

            await switchTo(page, 'estudiante');
            await page.goto(thread);
            await page.getByRole('button', { name: 'Reabrir' }).click();
            await expect(notices(page).getByText('Hilo reabierto.')).toBeVisible();
            await expect(page.getByLabel('Tu respuesta')).toBeVisible();
        });
    });
});
