import { expect, test } from '../fixtures/ui.js';

test.describe('Menús', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/_ui#ui-menu');
    });

    test('se abre con clic, cierra con Escape y devuelve el foco al disparador', async ({
        page,
        ui,
    }) => {
        const trigger = page.locator('button[aria-controls="demo-actions"]');
        const menu = page.locator('#demo-actions');

        await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await expect(menu).toBeHidden();

        await trigger.click();

        await expect(menu).toBeVisible();
        await expect(trigger).toHaveAttribute('aria-expanded', 'true');
        await expect(menu.getByRole('menuitem')).toHaveCount(4);
        await expect(menu.getByRole('separator')).toHaveCount(1);
        expect(await ui.axe('#ui-menu')).toEqual([]);

        await page.keyboard.press('Escape');

        await expect(menu).toBeHidden();
        await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await expect(trigger).toBeFocused();
    });

    test('la flecha abajo abre con el primer ítem enfocado y las flechas envuelven', async ({
        page,
    }) => {
        const trigger = page.locator('button[aria-controls="demo-actions"]');
        const items = page.locator('#demo-actions [role="menuitem"]');

        await trigger.focus();
        await page.keyboard.press('ArrowDown');

        await expect(items.nth(0)).toBeFocused();

        await page.keyboard.press('ArrowUp');
        await expect(items.nth(3)).toBeFocused();

        await page.keyboard.press('ArrowDown');
        await expect(items.nth(0)).toBeFocused();

        await page.keyboard.press('End');
        await expect(items.nth(3)).toBeFocused();

        await page.keyboard.press('Home');
        await expect(items.nth(0)).toBeFocused();

        // A letter jumps to the next item that starts with it
        await page.keyboard.press('a');
        await expect(items.nth(2)).toBeFocused();
        await page.keyboard.press('b');
        await expect(items.nth(3)).toBeFocused();

        await page.keyboard.press('Escape');
        await expect(trigger).toBeFocused();
    });

    test('Intro abre, enfoca el primer ítem y Tab cierra con aria-expanded false', async ({
        page,
    }) => {
        const trigger = page.locator('button[aria-controls="demo-actions"]');
        const menu = page.locator('#demo-actions');

        await trigger.focus();
        await page.keyboard.press('Enter');

        await expect(menu).toBeVisible();
        await expect(trigger).toHaveAttribute('aria-expanded', 'true');
        await expect(menu.getByRole('menuitem').first()).toBeFocused();

        await page.keyboard.press('Tab');

        await expect(menu).toBeHidden();
        await expect(trigger).toHaveAttribute('aria-expanded', 'false');

        // Space opens it the same way, also from a trigger passed in the slot
        const user = page.locator('button[aria-controls="demo-user"]');

        await user.focus();
        await page.keyboard.press(' ');

        await expect(page.locator('#demo-user')).toBeVisible();
        await expect(page.locator('#demo-user').getByRole('menuitem').first()).toBeFocused();
    });

    test('clic abre y Tab cierra', async ({ page }) => {
        const trigger = page.locator('button[aria-controls="demo-actions"]');
        const menu = page.locator('#demo-actions');

        await trigger.click();

        await expect(menu).toBeVisible();
        // The pointer opens the menu without moving the focus away from the trigger
        await expect(trigger).toBeFocused();

        await page.keyboard.press('Tab');

        await expect(menu).toBeHidden();
        await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await expect(trigger).not.toBeFocused();
    });

    test('un clic en la cabecera del menú de usuario no lo cierra', async ({ page }) => {
        const trigger = page.locator('button[aria-controls="demo-user"]');
        const menu = page.locator('#demo-user');

        await trigger.click();
        await expect(menu).toBeVisible();

        await menu.getByText('Estudiante · Coordinador de Programa').click();

        await expect(menu).toBeVisible();
        await expect(trigger).toHaveAttribute('aria-expanded', 'true');
    });

    test('un clic fuera y el tabulador cierran el menú sin mover el foco al disparador', async ({
        page,
    }) => {
        const trigger = page.locator('button[aria-controls="demo-actions"]');
        const menu = page.locator('#demo-actions');

        await trigger.click();
        await expect(menu).toBeVisible();

        await page.getByRole('heading', { name: 'Menús' }).click();
        await expect(menu).toBeHidden();
        await expect(trigger).not.toBeFocused();

        await trigger.focus();
        await page.keyboard.press('ArrowDown');
        await expect(menu.locator('[role="menuitem"]').first()).toBeFocused();

        await page.keyboard.press('Tab');
        await expect(menu).toBeHidden();
        await expect(trigger).not.toBeFocused();
    });

    test('el menú de usuario abre desde el avatar y cumple WCAG 2.2 AA abierto', async ({
        page,
        ui,
    }) => {
        // The topbar of the page has its own user menu with the same name; stay inside the section
        const trigger = ui.section('menu').getByRole('button', { name: 'Menú de Juan Pérez' });
        const menu = page.locator('#demo-user');

        await trigger.click();

        await expect(menu).toBeVisible();
        await expect(menu.getByRole('menuitem', { name: 'Salir' })).toBeVisible();
        await expect(menu.getByText('Estudiante · Coordinador de Programa')).toBeVisible();
        expect(await ui.axe('#ui-menu')).toEqual([]);
        await ui.noOverflow();
    });
});
