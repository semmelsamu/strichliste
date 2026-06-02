<x-layouts.main title="Artikel">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('dashboard') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Artikel</h1>
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

                    <td class="w-6">
                        <a href="{{ route("articles.edit", $article->id) }}">
                            <x-lucide-square-pen />
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </x-wrapper>
</x-layouts.main>
