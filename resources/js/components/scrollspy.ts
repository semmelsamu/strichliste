/**
 * Scrollspy Alpine.js component.
 *
 * Tracks which group section is currently visible in a scrollable container
 * and exposes it as `activeGroup`. Also lets the user press and drag over
 * the nav (like the iOS Contacts A-Z index) to scroll the container to the
 * section under the pointer as it moves, without lifting the finger between
 * letters.
 *
 * Usage:
 * - Add `x-data="scrollspy(<initialGroupId>)"` to a parent element.
 * - Add `x-ref="scrollContainer"` to the scrollable container.
 * - Add `data-group-id="<id>"` to each `<section>` inside the container.
 * - Add `x-ref="nav"` to the nav containing `<a href="#sectionId">` links.
 * - Bind `activeGroup` to highlight the corresponding nav item.
 */
export default (initialGroup: any = null) => ({
    activeGroup: initialGroup,

    init() {
        const sections = [
            ...this.$refs.scrollContainer.querySelectorAll("section"),
        ] as HTMLElement[];
        const visible = new Set<HTMLElement>();

        const update = () => {
            for (const section of sections) {
                if (visible.has(section)) {
                    this.activeGroup = section.dataset.groupId!;
                    return;
                }
            }
        };

        const scrollPaddingTop =
            parseFloat(
                getComputedStyle(this.$refs.scrollContainer).scrollPaddingTop,
            ) || 0;

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((e) =>
                    e.isIntersecting
                        ? visible.add(e.target as HTMLElement)
                        : visible.delete(e.target as HTMLElement),
                );
                update();
            },
            {
                root: this.$refs.scrollContainer,
                rootMargin: `-${scrollPaddingTop}px 0px 0px 0px`,
                threshold: 0,
            },
        );

        sections.forEach((s) => observer.observe(s));

        this.initDragScroll();
    },

    initDragScroll() {
        const nav = this.$refs.nav as HTMLElement | undefined;
        if (!nav) {
            return;
        }

        let lastHref: string | null = null;

        const scrollToPoint = (clientX: number, clientY: number) => {
            const link = document
                .elementFromPoint(clientX, clientY)
                ?.closest<HTMLAnchorElement>("a[href^='#']");

            if (!link || !nav.contains(link) || link.hash === lastHref) {
                return;
            }

            const target = document.getElementById(link.hash.slice(1));
            if (!target) {
                return;
            }

            lastHref = link.hash;
            target.scrollIntoView({ block: "start" });
        };

        const onMove = (e: PointerEvent) => {
            e.preventDefault();
            scrollToPoint(e.clientX, e.clientY);
        };

        const onUp = () => {
            lastHref = null;
            document.removeEventListener("pointermove", onMove);
            document.removeEventListener("pointerup", onUp);
        };

        nav.addEventListener("dragstart", (e) => e.preventDefault());

        nav.addEventListener("pointerdown", () => {
            document.addEventListener("pointermove", onMove);
            document.addEventListener("pointerup", onUp);
        });
    },
});
