<x-layouts.main title="Artikel">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('dashboard') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Artikel</h1>
        <a
            class="button ml-auto bg-fsim-light"
            href="{{ route('articles.create') }}"
        >
            <x-lucide-plus />
            Neuen Artikel erstellen
        </a>
    </header>
    <x-wrapper>
        <table class="table">
            <tr>
                <th>Name</th>
                <th>Kategorie</th>
                <th class="text-right">Preis</th>
            </tr>
            @foreach ($articles as $article)
                <tr class="border-t border-text-secondary/40 *:py-4">
                    <td class="w-auto font-medium">{{ $article->name }}</td>

                    <td>{{ $article->category->name }}</td>

                    <td class="text-right">
                        <x-currency
                            :amount="$article->currentPrice"
                            :colors="false"
                        />
                    </td>

                    <td class="flex items-center justify-end">
                        <a href="{{ route("articles.edit", $article->id) }}">
                            <x-lucide-square-pen />
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>

        <h2 class="mt-section mb-inline">Archiv</h2>

        <p class="mb-content text-text-secondary">Diese Artikel wurden archiviert und werden nicht mehr im Kaufmenü angezeigt.</p>

        <table class="table">
            <tr>
                <th>Name</th>
                <th>Gelöscht</th>
            </tr>

            @foreach ($archivedArticles as $article)
                <tr class="border-t border-text-secondary/40 *:py-4">
                    <td>{{ $article->name }}</td>
                    <td>{{ $article->deleted_at->diffForHumans() }}</td>
                </tr>
            @endforeach
        </table>
    </x-wrapper>
</x-layouts.main>
