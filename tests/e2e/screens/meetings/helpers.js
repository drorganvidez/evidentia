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
    await expect(notice(page, text)).toBeVisible();
}

/** The notice of the session flash, inside the live region of the layout. */
export function notice(page, text) {
    return page.getByRole('region', { name: 'Avisos' }).getByText(text);
}
