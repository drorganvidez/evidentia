import { expect, test } from './fixtures/ui.js';

/*
 * Touch targets of every page of the catalogue under a coarse pointer. axe only asks for 24 px (WCAG 2.5.8);
 * the plan asks for 44 × 44 px (§1.4). Links inside running text are the only exception: their block holds
 * text of its own on the same lines.
 */
const PAGES = ['/_ui', '/_ui/shell', '/_ui/detail', '/_ui/public'];

const TARGETS =
    'a[href], button, summary, input:not([type="hidden"]), select, textarea, [role="menuitem"]';

const MIN = 44;

/** Runs in the page: every visible target smaller than the minimum, with its name, size and place. */
function offenders({ selector, min }) {
    const inline = (el) => ['inline', 'contents'].includes(getComputedStyle(el).display);

    // Nearest ancestor that lays out lines: the block a piece of inline content belongs to
    const blockOf = (el) => {
        let block = el;

        while (block && inline(block)) {
            block = block.parentElement;
        }

        return block;
    };

    const inRunningText = (link) => {
        const block = blockOf(link.parentElement);

        if (!block) {
            return false;
        }

        const walker = document.createTreeWalker(block, NodeFilter.SHOW_TEXT);

        for (let node = walker.nextNode(); node; node = walker.nextNode()) {
            if (
                node.textContent.trim() !== '' &&
                !link.contains(node) &&
                blockOf(node.parentElement) === block
            ) {
                return true;
            }
        }

        return false;
    };

    const nameOf = (el) => {
        const labelledBy = (el.getAttribute('aria-labelledby') ?? '')
            .split(/\s+/)
            .map((id) => document.getElementById(id)?.textContent ?? '')
            .join(' ');
        const name =
            el.getAttribute('aria-label') ||
            labelledBy.trim() ||
            el.labels?.[0]?.textContent ||
            el.textContent ||
            el.getAttribute('title') ||
            el.getAttribute('placeholder') ||
            el.getAttribute('name') ||
            el.tagName.toLowerCase();

        return name.replace(/\s+/g, ' ').trim();
    };

    const placeOf = (el) => {
        const section = el.closest('[id^="ui-"]');

        if (section) {
            return `#${section.id}`;
        }

        const landmark = el.closest('header, nav, aside, footer, main, dialog');

        return landmark
            ? `${landmark.tagName.toLowerCase()}${landmark.getAttribute('aria-label') ? `[aria-label="${landmark.getAttribute('aria-label')}"]` : ''}`
            : 'body';
    };

    return Array.from(document.querySelectorAll(selector))
        .filter((el) => el.checkVisibility({ checkVisibilityCSS: true, visibilityProperty: true }))
        .filter((el) => getComputedStyle(el).pointerEvents !== 'none')
        .map((el) => ({ el, box: el.getBoundingClientRect() }))
        .filter(({ box }) => box.width > 1 && box.height > 1)
        .filter(({ box }) => box.width < min - 0.5 || box.height < min - 0.5)
        .filter(({ el }) => !(el.tagName === 'A' && inRunningText(el)))
        .map(
            ({ el, box }) =>
                `«${nameOf(el)}» <${el.tagName.toLowerCase()}> ${Math.round(box.width)} × ${Math.round(box.height)} en ${placeOf(el)}`,
        );
}

test.describe('Objetivos táctiles', () => {
    for (const path of PAGES) {
        test(`en ${path} cada objetivo visible mide al menos 44 × 44 px`, async ({
            page,
        }, testInfo) => {
            test.skip(
                testInfo.project.name !== 'mobile',
                'El objetivo de 44 px se exige con puntero grueso',
            );
            test.setTimeout(60_000);

            await page.goto(path);
            expect(await page.evaluate(() => matchMedia('(pointer: coarse)').matches)).toBe(true);

            const found = await page.evaluate(offenders, { selector: TARGETS, min: MIN });

            expect(found, `Objetivos por debajo de 44 × 44 px:\n${found.join('\n')}`).toEqual([]);
        });
    }
});
