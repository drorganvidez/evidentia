import { expect } from '../../fixtures/ui.js';

/** Waits until the page stops moving: loaded, with its fonts swapped in. */
async function settled(page) {
    await page.waitForLoadState('load');
    await page.evaluate(() => document.fonts.ready.then(() => true));
}

/** Audits the page in the light and the dark theme and checks the reflow in both. */
export async function auditThemes(page, ui) {
    await settled(page);
    expect(await ui.axe(), 'WCAG en claro').toEqual([]);
    await ui.noOverflow();
    await ui.setTheme('dark');
    await settled(page);
    expect(await ui.axe(), 'WCAG en oscuro').toEqual([]);
    await ui.noOverflow();
    await ui.setTheme('system');
}

/**
 * Flows that change data run once, in the desktop project, and only touch the rows they create, so the order of
 * the tests never matters. Call it inside a describe.
 */
export function mutatesData(test) {
    test.skip(({ isMobile }) => isMobile, 'Cambia datos');
}

/** A title unique to this run, to find the rows the flow creates among the seeded ones. */
export function unique(prefix) {
    return `${prefix} ${Date.now().toString(36)}`;
}

/** Confirms the alert dialog opened by the trigger and waits for the notice of the next page. */
export async function confirmRemoval(page, trigger, title, text) {
    await trigger.click();
    const dialog = page.getByRole('alertdialog', { name: title });
    await expect(dialog).toBeVisible();
    await expect(dialog.getByRole('button', { name: 'Cancelar' })).toBeFocused();
    await dialog.getByRole('button', { name: 'Sí, eliminar' }).click();
    await expectNotice(page, text);
}

/**
 * The notice of the session flash, inside the live region of the layout. Saving a call or minutes renders a PDF
 * first, so the next page may take a while; the notice then stays for 8 s.
 */
export async function expectNotice(page, text) {
    await expect(page.getByRole('region', { name: 'Avisos' }).getByText(text)).toBeVisible({
        timeout: 20_000,
    });
}

/** Waits for the people picker to take over its native select, so a choice is never made before Alpine listens. */
export async function pickerReady(page) {
    await expect(page.locator('select[name="users[]"]')).toHaveAttribute('aria-hidden', 'true');
}

/** Chooses the first default list in the preset select of the picker and loads it in place of the selection. */
export async function loadFirstPreset(page) {
    await page.getByRole('combobox', { name: 'Lista predeterminada' }).click();
    await page
        .getByRole('listbox', { name: 'Lista predeterminada' })
        .getByRole('option')
        .first()
        .click();
    await page.getByRole('button', { name: 'Cargar' }).click();
    await page.getByRole('button', { name: 'Sustituir' }).click();
}

/** Waits until Alpine has bound every component of the page, so typing never lands before its listeners. */
export async function alpineReady(page) {
    await page.waitForFunction(() =>
        Array.from(document.querySelectorAll('[x-data]')).every((element) => element._x_dataStack),
    );
}
