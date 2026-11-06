import { signIn } from '../../fixtures/auth.js';
import { expect, test } from '../../fixtures/ui.js';
import { audit, evidenceId, notices, publishEvidence, switchTo, unique } from './helpers.js';

// The history of an evidence and the corrections made from it: change of committee, move by the coordinator and
// review by the management
test.describe('Historial de una evidencia', () => {
    // several pages and two audits of axe per test, slow on the emulated phone
    test.slow();

    test('la autora ve el recorrido, el motivo y el diálogo de cambio de comité', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'estudiante');
        const id = await evidenceId(page, 'Recogida de material');
        await page.goto(`/activity/evidence/${id}`);

        await expect(
            page.getByRole('heading', { level: 1, name: 'Recogida de material' }),
        ).toBeVisible();
        await expect(
            page.getByRole('note').filter({ hasText: 'Motivo del rechazo' }),
        ).toContainText('Faltan las fotos del montaje terminado.');
        const sections = page.getByRole('navigation', { name: 'Secciones de la evidencia' });
        await expect(sections.getByRole('link', { name: 'Historial' })).toHaveAttribute(
            'aria-current',
            'page',
        );
        await expect(sections.getByRole('link', { name: 'Detalle' })).toHaveAttribute(
            'href',
            new RegExp(`/evidence/view/${id}$`),
        );
        await expect(page.getByRole('list', { name: 'Historial de la evidencia' })).toContainText(
            'Evidencia rechazada',
        );
        await expect(page.getByRole('link', { name: 'Escribir al coordinador' })).toHaveAttribute(
            'href',
            new RegExp(`/messages/create\\?evidence=${id}$`),
        );

        const trigger = page.getByRole('button', { name: 'Cambiar de comité' });
        await trigger.click();
        const dialog = page.getByRole('dialog', { name: 'Cambiar de comité' });
        await expect(dialog).toBeVisible();
        await expect(dialog.getByLabel('Comité de destino')).toBeVisible();
        expect(await ui.axe()).toEqual([]);
        await page.keyboard.press('Escape');
        await expect(dialog).toBeHidden();
        await expect(trigger).toBeFocused();

        await page.goto(`/activity/evidence/${id}?move=1`);
        await expect(page.getByRole('dialog', { name: 'Cambiar de comité' })).toBeVisible();
        await page.keyboard.press('Escape');
        await audit(page, ui);
    });

    test('el detalle enlaza su historial', async ({ page }) => {
        await signIn(page, 'estudiante');
        const id = await evidenceId(page, 'Montaje del photocall');
        await page.goto(`/evidence/view/${id}`);

        await page
            .getByRole('navigation', { name: 'Secciones de la evidencia' })
            .getByRole('link', { name: 'Historial' })
            .click();
        await expect(page).toHaveURL(new RegExp(`/activity/evidence/${id}$`));
        await expect(
            page.getByRole('heading', { level: 1, name: 'Montaje del photocall' }),
        ).toBeVisible();
    });

    test('la gestión ve las acciones de revisión', async ({ page, ui }) => {
        await signIn(page, 'estudiante');
        const id = await evidenceId(page, 'Montaje del photocall');
        await switchTo(page, 'profesor');
        await page.goto(`/activity/evidence/${id}`);

        await expect(page.getByRole('button', { name: 'Aceptar o rechazar' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Mover a otro comité' })).toBeVisible();
        await page.getByRole('button', { name: 'Aceptar o rechazar' }).click();
        await expect(page.getByRole('dialog', { name: 'Revisar la evidencia' })).toBeVisible();
        expect(await ui.axe()).toEqual([]);
        await page.keyboard.press('Escape');
        await audit(page, ui);
    });

    test.describe('correcciones', () => {
        test.skip(({ isMobile }) => isMobile, 'Cambia datos');

        test('la autora la cambia de comité, el coordinador la mueve y la gestión la rechaza', async ({
            page,
        }) => {
            const title = unique('Montaje del escenario');

            await signIn(page, 'estudiante');
            const id = await publishEvidence(page, title);
            await page.goto(`/activity/evidence/${id}`);

            await page.getByRole('button', { name: 'Cambiar de comité' }).click();
            let dialog = page.getByRole('dialog', { name: 'Cambiar de comité' });
            await dialog.getByLabel('Comité de destino').selectOption({ label: 'Comunicación' });
            await dialog.getByRole('button', { name: 'Mover' }).click();
            await expect(page).toHaveURL(new RegExp(`/activity/evidence/${id}$`));
            await expect(
                notices(page).getByText('Evidencia movida al comité Comunicación.'),
            ).toBeVisible();
            await expect(
                page.getByRole('list', { name: 'Historial de la evidencia' }),
            ).toContainText('Evidencia movida de comité');

            // back to Logística, where its coordinator sends it to Comunicación again
            await page.getByRole('button', { name: 'Cambiar de comité' }).click();
            dialog = page.getByRole('dialog', { name: 'Cambiar de comité' });
            await dialog.getByLabel('Comité de destino').selectOption({ label: 'Logística' });
            await dialog.getByRole('button', { name: 'Mover' }).click();
            await expect(
                notices(page).getByText('Evidencia movida al comité Logística.'),
            ).toBeVisible();

            await switchTo(page, 'coordinador');
            await page.goto('/coordinator/evidence/list/pending');
            await page.getByRole('link', { name: `Mover a otro comité ${title}` }).click();
            dialog = page.getByRole('dialog', { name: 'Mover a otro comité' });
            await expect(dialog).toBeVisible();
            await dialog.getByLabel('Comité de destino').selectOption({ label: 'Comunicación' });
            await dialog.getByRole('button', { name: 'Mover' }).click();
            await expect(page).toHaveURL(/\/coordinator\/evidence\/list\/pending$/);
            await expect(
                notices(page).getByText('Evidencia enviada al comité Comunicación.'),
            ).toBeVisible();
            await expect(page.getByRole('link', { name: title, exact: true })).toHaveCount(0);

            await switchTo(page, 'profesor');
            await page.goto(`/activity/evidence/${id}`);
            await page.getByRole('button', { name: 'Aceptar o rechazar' }).click();
            dialog = page.getByRole('dialog', { name: 'Revisar la evidencia' });
            await dialog.getByText('Rechazar', { exact: true }).click();
            await dialog.getByLabel('Motivo del rechazo').fill('Faltan las fotos del desmontaje.');
            await dialog.getByRole('button', { name: 'Guardar' }).click();
            await expect(notices(page).getByText('Evidencia rechazada.')).toBeVisible();
            await expect(
                page.getByRole('list', { name: 'Historial de la evidencia' }),
            ).toContainText('Evidencia rechazada');

            // the student cleans up
            await switchTo(page, 'estudiante');
            await page.goto(`/evidence/view/${id}`);
            await page.getByRole('button', { name: 'Eliminar' }).click();
            await page
                .getByRole('alertdialog')
                .getByRole('button', { name: 'Sí, eliminar' })
                .click();
            await expect(page.getByText('Evidencia borrada con éxito.').first()).toBeVisible();
        });
    });
});
