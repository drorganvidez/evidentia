import { expect, test } from '../fixtures/ui.js';

// The table of the catalogue ordered in the browser: relative dates by their instant, hours as numbers, people by
// the surname without accents, attendances in their logical order and the empty cells last
const names = (table) =>
    table
        .locator('tbody tr:not([hidden])')
        .evaluateAll((rows) =>
            rows.map((row) => row.querySelector('[data-sort-value]')?.dataset.sortValue),
        );

test.describe('Orden de las tablas en el navegador', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-table-sortable');
    });

    test('ordena las fechas relativas por su instante y alterna la dirección', async ({
        page,
        ui,
    }) => {
        test.skip(test.info().project.name === 'mobile', 'La cabecera solo se ve en el escritorio');
        const table = page.locator('#ui-table-sortable');
        const signed = table.getByRole('columnheader', { name: 'Inscrita' });

        await expect(signed).toHaveAttribute('aria-sort', 'descending');
        await expect(table.getByRole('columnheader', { name: 'Persona' })).not.toHaveAttribute(
            'aria-sort',
        );

        // «hace 1 mes», «hace 3 días», «hace 2 horas», «hace 10 minutos»: never the order of the text
        await signed.getByRole('button', { name: 'Inscrita' }).click();
        await expect(signed).toHaveAttribute('aria-sort', 'ascending');
        expect(await names(table)).toEqual([
            'Gómez Marta',
            'Álvarez Elena',
            'Navarro Carlos',
            'Pérez Lucía',
            'Torres Diego',
        ]);
        const instants = await table
            .locator('tbody time[datetime]')
            .evaluateAll((times) => times.map((time) => Date.parse(time.getAttribute('datetime'))));
        expect(instants).toEqual([...instants].sort((a, b) => a - b));

        await signed.getByRole('button', { name: 'Inscrita' }).click();
        await expect(signed).toHaveAttribute('aria-sort', 'descending');
        expect(await names(table)).toEqual([
            'Pérez Lucía',
            'Navarro Carlos',
            'Álvarez Elena',
            'Gómez Marta',
            'Torres Diego',
        ]);
        await expect(
            page.getByRole('status').filter({ hasText: 'Ordenada por Inscrita' }),
        ).toHaveCount(1);
        await ui.noOverflow();
    });

    test('se ordena con el teclado por personas, horas y estados', async ({ page }) => {
        test.skip(test.info().project.name === 'mobile', 'La cabecera solo se ve en el escritorio');
        const table = page.locator('#ui-table-sortable');
        const person = table.getByRole('button', { name: 'Persona' });

        await person.focus();
        await page.keyboard.press('Enter');
        await expect(table.getByRole('columnheader', { name: 'Persona' })).toHaveAttribute(
            'aria-sort',
            'ascending',
        );
        await expect(table.getByRole('columnheader', { name: 'Inscrita' })).not.toHaveAttribute(
            'aria-sort',
        );
        await expect(person).toBeFocused();
        expect(await names(table)).toEqual([
            'Álvarez Elena',
            'Gómez Marta',
            'Navarro Carlos',
            'Pérez Lucía',
            'Torres Diego',
        ]);

        await page.keyboard.press('Tab');
        await expect(table.getByRole('button', { name: 'Inscrita' })).toBeFocused();
        await page.keyboard.press('Tab');
        await expect(table.getByRole('button', { name: 'Horas' })).toBeFocused();
        await page.keyboard.press('Space');
        // 0,5 before 1,25 before 2 before 10, and the row without hours at the end
        expect(await names(table)).toEqual([
            'Navarro Carlos',
            'Gómez Marta',
            'Pérez Lucía',
            'Álvarez Elena',
            'Torres Diego',
        ]);

        await table.getByRole('button', { name: 'Estado' }).click();
        expect(await names(table)).toEqual([
            'Navarro Carlos',
            'Pérez Lucía',
            'Gómez Marta',
            'Álvarez Elena',
            'Torres Diego',
        ]);
        await expect(table.getByRole('button', { name: 'Acciones' })).toHaveCount(0);
    });

    test('convive con el filtro rápido', async ({ page }) => {
        test.skip(test.info().project.name === 'mobile', 'La cabecera solo se ve en el escritorio');
        const table = page.locator('#ui-table-sortable');

        await table.getByRole('searchbox', { name: 'Filtrar inscripciones' }).fill('ez');
        await expect(table.getByText('3 de 5 inscripciones')).toBeVisible();
        await table.getByRole('button', { name: 'Persona' }).click();
        expect(await names(table)).toEqual(['Álvarez Elena', 'Gómez Marta', 'Pérez Lucía']);

        await table.getByRole('searchbox', { name: 'Filtrar inscripciones' }).fill('');
        await expect(table.getByText('5 inscripciones', { exact: true })).toBeVisible();
        expect(await names(table)).toHaveLength(5);
        expect((await names(table))[0]).toBe('Álvarez Elena');
    });

    test('en el teléfono se ordena con el desplegable del sistema', async ({ page, ui }) => {
        test.skip(test.info().project.name !== 'mobile', 'El control solo se ve en el teléfono');
        const table = page.locator('#ui-table-sortable');
        const order = table.getByRole('combobox', { name: 'Ordenar por' });

        await expect(order).toBeVisible();
        await expect(order).toHaveValue('1:desc');
        const box = await order.boundingBox();
        expect(box.height).toBeGreaterThanOrEqual(44);

        await order.selectOption({ label: 'Horas, ascendente' });
        expect(await names(table)).toEqual([
            'Navarro Carlos',
            'Gómez Marta',
            'Pérez Lucía',
            'Álvarez Elena',
            'Torres Diego',
        ]);

        await order.selectOption({ label: 'Inscrita, ascendente' });
        expect((await names(table))[0]).toBe('Gómez Marta');
        await ui.noOverflow();
    });

    test('pasa axe en claro y oscuro', async ({ page, ui }) => {
        expect(await ui.axe('#ui-table-sortable')).toEqual([]);

        await ui.setTheme('dark');
        await page.locator('#ui-table-sortable').scrollIntoViewIfNeeded();
        expect(await ui.axe('#ui-table-sortable')).toEqual([]);
    });
});
