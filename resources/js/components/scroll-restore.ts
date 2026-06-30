/**
 * Restores window scroll position across full page reloads / redirects.
 *
 * The current page's scroll position is stored continuously. When a new page
 * loads, the position is reapplied only if it is the same page (same path),
 * so POST → redirect()->back() flows land where the user was scrolled.
 */
const KEY = "scroll-restore";

type StoredPosition = {
    path: string;
    y: number;
};

function read(): StoredPosition | null {
    try {
        return JSON.parse(sessionStorage.getItem(KEY) ?? "null");
    } catch {
        return null;
    }
}

export default function scrollRestore(): void {
    // Stop the browser from also trying to manage scroll on back/forward.
    if ("scrollRestoration" in history) {
        history.scrollRestoration = "manual";
    }

    const save = () => {
        const position: StoredPosition = {
            path: location.pathname,
            y: window.scrollY,
        };
        sessionStorage.setItem(KEY, JSON.stringify(position));
    };

    // pagehide is the reliable "leaving the page" event (incl. form submits).
    window.addEventListener("pagehide", save);

    const stored = read();
    if (stored && stored.path === location.pathname) {
        window.scrollTo(0, stored.y);
    }
}
