<x-layouts.tally-sheet :title="$category->name . ' kaufen'" activeTab="buy">
    <x-wrapper class="space-y-content">
        <a href="{{ route('tally-sheet.buy-overview') }}" class="button">
            <x-lucide-chevron-left /> Zurück
        </a>

        <h2>{{ $category->name }}</h2>

        <div class="grid grid-cols-4 gap-inline">
            @foreach ($category->articles as $article)
                <x-article-card :article="$article" />
            @endforeach
        </div>
    </x-wrapper>
</x-layouts.tally-sheet>
