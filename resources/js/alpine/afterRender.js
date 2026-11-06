/**
 * Runs a callback once Alpine has shown what the last update revealed. x-show shows on the next animation
 * frame, so a callback queued after it (from $nextTick) finds the element visible; with the page hidden
 * there are no frames and a timer takes their place. Shared by the modules that move the focus or scroll
 * to an element that has just appeared; not an Alpine component.
 */
export function afterRender(callback) {
    if (document.visibilityState === 'visible') {
        requestAnimationFrame(callback);
    } else {
        setTimeout(callback, 0);
    }
}
