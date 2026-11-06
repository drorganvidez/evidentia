import { signIn } from '../../fixtures/auth.js';
import { choose, expect, test } from '../../fixtures/ui.js';

// The order of the lists on the server: every column, the dates by their timestamp whatever the text says, the
// filters kept in the links and back on the first page; on the phone, the select of the system
const isPhone = () => test.info().project.name === 'mobile';

async function titles(table) {
    return table.locator('tbody tr td:first-child a').allTextContents();
}

async function instants(table, column) {
    return table
        .locator(`tbody tr td:nth-child(${column}) time[datetime]`)
        .evaluateAll((times) => times.map((time) => Date.parse(time.getAttribute('datetime'))));
}

test.describe('Orden de las listas en el servidor', () => {
    test('las evidencias del curso se ordenan por la fecha real y por el autor', async ({
        page,
        ui,
    }) => {
        test.skip(isPhone(), 'La cabecera solo se ve en el escritorio');
        await signIn(page, 'profesor');
        // Other specs review and add evidences: the checks read the rows there are, not a fixed list
        await page.goto('/lecture/evidence/list?hasta=2099-12-31&page=2');

        const table = page.getByRole('region', { name: 'Evidencias del curso' });
        const created = table.getByRole('columnheader', { name: 'Creada' });

        // The default order is the last change, which no column shows
        await expect(table.locator('th[aria-sort]')).toHaveCount(0);

        await created.getByRole('link', { name: 'Creada' }).click();
        await expect(page).toHaveURL(/hasta=2099-12-31&sort=created_at&dir=desc$/);
        await expect(created).toHaveAttribute('aria-sort', 'descending');
        const newest = await instants(table, 5);
        expect(newest.length).toBeGreaterThan(1);
        expect(newest).toEqual([...newest].sort((a, b) => b - a));
        const descending = await titles(table);

        await created.getByRole('link', { name: 'Creada' }).click();
        await expect(page).toHaveURL(/sort=created_at&dir=asc$/);
        await expect(created).toHaveAttribute('aria-sort', 'ascending');
        const oldest = await instants(table, 5);
        expect(oldest).toEqual([...oldest].sort((a, b) => a - b));
        // Ties go by the key in the same direction, so one order is exactly the other reversed
        expect(await titles(table)).toEqual([...descending].reverse());
        await expect(
            page
                .getByRole('navigation', { name: 'Filtrar evidencias por estado' })
                .getByRole('link', { name: /Pendientes/ }),
        ).toHaveAttribute('href', /hasta=2099-12-31&sort=created_at&dir=asc&estado=pendientes/);

        // People by surname, García before Pérez; the keyboard follows the link of the header
        await table.getByRole('link', { name: 'Autor', exact: true }).focus();
        await page.keyboard.press('Enter');
        await expect(page).toHaveURL(/sort=author&dir=asc$/);
        await expect(table.getByRole('columnheader', { name: 'Autor' })).toHaveAttribute(
            'aria-sort',
            'ascending',
        );
        const authors = await table
            .locator('tbody tr td:nth-child(2) [data-sort-value]')
            .evaluateAll((people) => people.map((person) => person.dataset.sortValue));
        const lastGarcia = authors.map((name) => name.startsWith('García')).lastIndexOf(true);
        const firstPerez = authors.findIndex((name) => name.startsWith('Pérez'));
        expect(lastGarcia).toBeGreaterThanOrEqual(0);
        expect(lastGarcia).toBeLessThan(firstPerez);
        await ui.noOverflow();
    });

    test('un orden que no existe se ignora', async ({ page }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/evidence/list?sort=password&dir=sideways');

        const table = page.getByRole('region', { name: 'Evidencias del curso' });
        await expect(
            table.getByRole('link', { name: 'Montaje del photocall', exact: true }),
        ).toBeVisible();
        await expect(table.locator('th[aria-sort]')).toHaveCount(0);
    });

    test('en el teléfono las evidencias se ordenan con el desplegable', async ({ page, ui }) => {
        test.skip(!isPhone(), 'El control solo se ve en el teléfono');
        await signIn(page, 'profesor');
        await page.goto('/lecture/evidence/list?comite=1');

        const order = page
            .getByRole('region', { name: 'Evidencias del curso' })
            .getByRole('combobox', { name: 'Ordenar por' });
        await expect(order).toBeVisible();
        expect((await order.boundingBox()).height).toBeGreaterThanOrEqual(44);

        await order.selectOption({ label: 'Creada, ascendente' });
        await expect(page).toHaveURL(/comite=1&sort=created_at&dir=asc$/);
        await expect(page.getByRole('combobox', { name: 'Ordenar por' })).toHaveValue(
            /sort=created_at&dir=asc$/,
        );
        await ui.noOverflow();
    });

    test('los alumnos se ordenan por cualquier columna sin perder los filtros', async ({
        page,
        ui,
    }) => {
        await signIn(page, 'profesor');
        await page.goto('/lecture/user/list');
        const table = page.getByRole('region', { name: 'Usuarios' });
        const people = () =>
            table.locator('tbody tr').getByRole('link', { name: /,/ }).allTextContents();

        await page.getByText('Filtros').click();
        await choose(page.getByRole('combobox', { name: 'Rol', exact: true }), 'Estudiante');
        await page.getByRole('button', { name: 'Aplicar' }).click();
        await expect(page).toHaveURL(/rol=STUDENT/);

        if (isPhone()) {
            await table
                .getByRole('combobox', { name: 'Ordenar por' })
                .selectOption({ label: 'UVUS, descendente' });
        } else {
            await table.getByRole('link', { name: 'UVUS', exact: true }).click();
            await expect(page).toHaveURL(/rol=STUDENT&sort=username&dir=asc$/);
            await table.getByRole('link', { name: 'UVUS', exact: true }).click();
        }
        await expect(page).toHaveURL(/rol=STUDENT&sort=username&dir=desc$/);
        let names = await people();
        // estudiante before companero, descending by UVUS
        expect(names.indexOf('Pérez Romero, Lucía')).toBeLessThan(
            names.indexOf('García López, Juan'),
        );

        // The filters resend the order
        const apply = page.getByRole('button', { name: 'Aplicar' });
        if (!(await apply.isVisible())) {
            await page.getByText('Filtros').click();
        }
        await apply.click();
        await expect(page).toHaveURL(/sort=username&dir=desc/);
        names = await people();
        expect(names.indexOf('Pérez Romero, Lucía')).toBeLessThan(
            names.indexOf('García López, Juan'),
        );
        await ui.noOverflow();
    });

    test('las listas ordenadas pasan axe en claro y oscuro', async ({ page, ui }) => {
        await signIn(page, 'profesor');
        for (const path of [
            '/lecture/evidence/list?sort=created_at&dir=asc',
            '/lecture/user/list?sort=username&dir=desc',
        ]) {
            await page.goto(path);
            expect(await ui.axe()).toEqual([]);

            await ui.setTheme('dark');
            expect(await ui.axe()).toEqual([]);
            await ui.setTheme('light');
        }
    });
});
