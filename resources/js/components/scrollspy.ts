/**
 * Scrollspy Alpine.js component.
 *
 * Tracks which group section is currently visible in a scrollable container
 * and exposes it as `activeGroup`.
 *
 * Usage:
 * - Add `x-data="scrollspy(<initialGroupId>)"` to a parent element.
 * - Add `x-ref="scrollContainer"` to the scrollable container.
 * - Add `data-group-id="<id>"` to each `<section>` inside the container.
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

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((e) =>
                    e.isIntersecting
                        ? visible.add(e.target as HTMLElement)
                        : visible.delete(e.target as HTMLElement),
                );
                update();
            },
            { root: this.$refs.scrollContainer, threshold: 0 },
        );

        sections.forEach((s) => observer.observe(s));
    },
});
