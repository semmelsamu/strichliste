<x-layout.main :title="$category->name . ' kaufen'">
    <x-header.tally-sheet
        activeTab="buy"
        class="space-y-content bg-fsim-dark pt-section pb-content"
    >
        <a
            href="{{ route('tally-sheet.buy-overview') }}"
            class="button w-fit bg-fsim-medium"
        >
            <x-lucide-chevron-left /> Zurück
        </a>

        <h2>{{ $category->name }}</h2>
    </x-header.tally-sheet>

    <x-wrapper class="pt-0">
        <div class="grid grid-cols-4 gap-inline">
            @foreach ($category->articles as $article)
                <x-article-card :article="$article" />
            @endforeach
        </div>
    </x-wrapper>
</x-layout.main>
