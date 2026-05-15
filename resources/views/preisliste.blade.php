<x-layouts.app title="Preisliste">
    <header class="bg-fsim-medium p-wrapper">
        <h1>Preisliste</h1>
    </header>
    <x-wrapper
        class="grid grid-cols-[1fr_3fr] gap-content overflow-hidden p-wrapper"
        x-data="scrollspy({{ $categories->first()?->id ?? 'null' }})"
    >
        <nav class="flex flex-col gap-inline overflow-y-auto">
            @foreach ($categories as $category)
                <a
                    href="#category-{{ $category->id }}"
                    class="p-inline"
                    :class="activeGroup === {{ $category->id }} ? 'card' : ''"
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </nav>

        <div
            x-ref="scrollContainer"
            class="flex flex-col gap-section overflow-y-auto"
        >
            @foreach ($categories as $category)
                <section
                    id="category-{{ $category->id }}"
                    data-group-id="{{ $category->id }}"
                >
                    <h2 class="mb-inline">{{ $category->name }}</h2>
                    <div class="flex flex-col gap-inline">
                        @foreach ($category->products as $product)
                            <div
                                class="card flex items-center justify-between p-inline"
                            >
                                <span>{{ $product->name }}</span>
                                <span class="text-text-secondary"
                                    >{{ number_format($product->price, 2, ',', '') }} €</span
                                >
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </x-wrapper>
</x-layouts.app>
