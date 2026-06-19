/**
 * Inactivity timeout Alpine.js component.
 *
 * Redirects to the given URL once the page has seen no mouse, keyboard, or
 * touch input for INACTIVITY_TIMEOUT. Used on the tally sheet's logged-in
 * customer views, which run on a shared kiosk device, to log out a customer
 * who walked away without explicitly logging out.
 *
 * Usage:
 * - Add `x-data="inactivityTimeout('<redirectUrl>')"` to any element.
 */
const INACTIVITY_TIMEOUT = 30_000;

export default (redirectUrl: string) => ({
    timer: undefined as ReturnType<typeof setTimeout> | undefined,

    init() {
        this.resetTimer();

        ["mousedown", "mousemove", "keydown", "touchstart", "scroll"].forEach(
            (event) =>
                document.addEventListener(event, () => this.resetTimer()),
        );
    },

    resetTimer() {
        clearTimeout(this.timer);
        this.timer = setTimeout(
            () => window.location.assign(redirectUrl),
            INACTIVITY_TIMEOUT,
        );
    },
});
