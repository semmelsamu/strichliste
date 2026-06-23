<x-layout.main title="Preisliste" class="print:max-h-auto max-h-screen">
    <x-header class="bg-fsim-medium p-wrapper">
        <h1>Preisliste</h1>
    </x-header>

    <main
        class="grid flex-1 grid-cols-[1fr_3fr] overflow-hidden print:block print:overflow-visible"
        x-data="scrollspy({{ $categories->first()?->id ?? 'null' }})"
    >
        <nav
            x-ref="nav"
            class="flex touch-none flex-col gap-inline overflow-y-auto py-section pl-wrapper select-none print:hidden"
        >
            @foreach ($categories as $category)
                <a
                    href="#category-{{ $category->id }}"
                    class="p-inline"
                    :class="activeGroup == {{ $category->id }} ? 'card' : ''"
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </nav>

        <div
            x-ref="scrollContainer"
            class="flex scroll-p-section flex-col gap-section overflow-y-auto py-section pr-wrapper pl-content print:overflow-y-visible print:p-0"
        >
            @foreach ($categories as $category)
                <section
                    id="category-{{ $category->id }}"
                    data-group-id="{{ $category->id }}"
                    class="print:break-inside-avoid"
                >
                    <h2 class="mb-inline">{{ $category->name }}</h2>
                    <div
                        class="flex flex-col gap-inline print:gap-0 print:divide-y-1"
                    >
                        @foreach ($category->articles as $article)
                            <div
                                class="card flex items-center justify-between p-inline print:p-0 print:shadow-none"
                            >
                                <span>{{ $article->name }}</span>
                                <x-currency
                                    :amount="$article->currentPrice"
                                    :colors="false"
                                    class="text-text-secondary"
                                />
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </main>
</x-layout.main>
