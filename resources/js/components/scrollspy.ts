export default (initialGroup: number | null = null) => ({
    activeGroup: initialGroup,

    init() {
        const sections = [
            ...this.$refs.scrollContainer.querySelectorAll("section"),
        ] as HTMLElement[];
        const visible = new Set<HTMLElement>();

        const update = () => {
            for (const section of sections) {
                if (visible.has(section)) {
                    this.activeGroup = parseInt(section.dataset.groupId!);
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
