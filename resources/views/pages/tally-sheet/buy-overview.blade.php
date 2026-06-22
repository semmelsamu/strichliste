<x-layouts.tally-sheet title="Artikel kaufen" activeTab="buy">
    <x-wrapper class="space-y-section">
        <x-note>
            Du kannst Artikel entweder mit dem Barcodescanner einscannen oder
            sie hier auswählen.
        </x-note>
        <section class="space-y-inline">
            <h2>Häufig gekauft</h2>
            <div class="grid grid-cols-4 gap-inline">
                @forelse ($mostFrequentArticles as $article)
                    <x-article-card :article="$article" />
                @empty
                    <p class="col-span-full p-section text-center text-text-secondary">Häufig gekaufte Artikel werden hier angezeigt, allerdings hast du keine Artikel in letzter Zeit gekauft.</p>
                @endforelse
            </div>
        </section>
        <section class="space-y-inline">
            <h2>Alle Kategorien</h2>
            <div class="grid grid-cols-3 gap-inline">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('tally-sheet.buy-categories', $category->id) }}"
                        class="card block flex flex-col items-center gap-inline p-section text-center"
                    >
                        @svg ($category->icon, "w-8 h-8")
                        <h3>{{ $category->name }}</h3>
                    </a>
                @endforeach
            </div>
        </section>
    </x-wrapper>
</x-layouts.tally-sheet>
