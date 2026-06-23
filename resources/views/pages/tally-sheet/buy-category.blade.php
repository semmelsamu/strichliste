<x-layout.tally-sheet :title="$category->name . ' kaufen'" activeTab="buy">
    <x-wrapper class="relative pt-0">
        <header
            class="sticky top-0 space-y-content bg-fsim-dark pt-section pb-content"
        >
            <a
                href="{{ route('tally-sheet.buy-overview') }}"
                class="button w-fit bg-fsim-medium"
            >
                <x-lucide-chevron-left /> Zurück
            </a>

            <h2>{{ $category->name }}</h2>
        </header>

        <div class="grid grid-cols-4 gap-inline">
            @foreach ($category->articles as $article)
                <x-article-card :article="$article" />
            @endforeach
        </div>
    </x-wrapper>
</x-layout.tally-sheet>
