<x-layout.main title="Artikel">
    <x-header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
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
    </x-header>
    <x-wrapper>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Kategorie</th>
                    <th class="text-right">Preis</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($articles as $article)
                    <tr>
                        <th class="w-auto">{{ $article->name }}</th>

                        <td>{{ $article->category->name }}</td>

                        <td class="text-right">
                            <x-currency
                                :amount="$article->currentPrice"
                                :colors="false"
                            />
                        </td>

                        <td class="flex items-center justify-end">
                            <a
                                href="{{ route("articles.edit", $article->id) }}"
                            >
                                <x-lucide-square-pen />
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <th colspan="4" class="text-center">
                            Keine Artikel gefunden.
                        </th>
                    </tr>
                @endforelse
            </tbody>
            <caption>
                {{ sizeof($articles) }} Artikel gesamt.
            </caption>
        </table>

        <h2 class="mt-section mb-inline">Archiv</h2>

        <p class="mb-content text-text-secondary">Diese Artikel wurden archiviert und werden nicht mehr im Kaufmenü angezeigt.</p>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Gelöscht</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($archivedArticles as $article)
                    <tr>
                        <td>{{ $article->name }}</td>
                        <td>{{ $article->deleted_at->diffForHumans() }}</td>
                        <td class="flex items-center justify-end">
                            <x-form
                                post="{{ route('articles.restore', $article->id) }}"
                                class="flex items-center"
                            >
                                <button type="submit">
                                    <x-lucide-archive-restore />
                                </button>
                            </x-form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <th colspan="2" class="text-center">
                            Keine archivierten Artikel gefunden.
                        </th>
                    </tr>
                @endforelse
            </tbody>

            <caption>
                {{ sizeof($archivedArticles) }} archivierte Artikel gesamt.
            </caption>
        </table>
    </x-wrapper>
</x-layout.main>
