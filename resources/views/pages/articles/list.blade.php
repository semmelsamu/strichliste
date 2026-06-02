<x-layouts.main title="Artikel">
    <header class="bg-fsim-medium p-wrapper">
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
                        <x-lucide-square-pen />
                    </td>
                </tr>
            @endforeach
        </table>
    </x-wrapper>
</x-layouts.main>
