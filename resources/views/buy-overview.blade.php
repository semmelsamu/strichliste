<x-layouts.tally-sheet title="Artikel kaufen" activeTab="buy" :user="$user">
    <x-wrapper class="space-y-section">
        <x-note>
            Du kannst Artikel entweder mit dem Barcodescanner einscannen oder
            sie hier auswählen.
        </x-note>
        <section class="space-y-inline">
            <h2>Häufig gekauft</h2>
            <div class="grid grid-cols-3 gap-inline">
                @foreach ($categories->first()->articles->take(3) as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        </section>
        <section class="space-y-inline">
            <h2>Alle Kategorien</h2>
            <div class="grid grid-cols-3 gap-inline">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('tally-sheet.buy-categories', [$user->id, $category->id]) }}"
                        class="card block flex flex-col items-center gap-inline p-content text-center"
                    >
                        @svg ($category->icon, "w-8 h-8")
                        <h3>{{ $category->name }}</h3>
                    </a>
                @endforeach
            </div>
        </section>
    </x-wrapper>
</x-layouts.tally-sheet>
